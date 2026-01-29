<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('admin')->user();
        
        // Check if audit_logs table exists
        try {
            $tableExists = \Schema::hasTable('audit_logs');
            if (!$tableExists) {
                return view('audit.setup');
            }
        } catch (\Exception $e) {
            return view('audit.setup');
        }
        
        // Get filter options for admin
        $users = $user->isAdmin() ? Admin::orderBy('full_name')->get() : collect();
        
        // Get system activity logs
        $systemQuery = AuditLog::with('admin');
        if (!$user->isAdmin()) {
            $systemQuery->forUser($user->id);
        }
        $systemQuery->whereIn('action', [
            'login', 'logout',
            'create_user', 'update_user', 'delete_user'
        ]);
        
        // Apply system filters
        if ($request->filled('system_user_id') && $user->isAdmin()) {
            $systemQuery->forUser($request->system_user_id);
        }
        if ($request->filled('system_module')) {
            $systemQuery->byModule($request->system_module);
        }
        if ($request->filled('system_action')) {
            $systemQuery->byAction($request->system_action);
        }
        if ($request->filled('system_days')) {
            $systemQuery->recent($request->system_days);
        } else {
            $systemQuery->recent(30);
        }
        
        $systemLogs = $systemQuery->orderBy('created_at', 'desc')->paginate(25, ['*'], 'system_page');
        
        // Get business activity logs
        $businessQuery = AuditLog::with('admin');
        if (!$user->isAdmin()) {
            $businessQuery->forUser($user->id);
        }
        $businessQuery->whereIn('action', [
            'create_sale', 'receipt_email_sent',
            'create_product', 'update_product', 'delete_product', 'stock_refill',
            'create_category', 'update_category', 'delete_category', 'toggle_category_status'
        ]);
        
        // Apply business filters
        if ($request->filled('business_user_id') && $user->isAdmin()) {
            $businessQuery->forUser($request->business_user_id);
        }
        if ($request->filled('business_module')) {
            $businessQuery->byModule($request->business_module);
        }
        if ($request->filled('business_action')) {
            $businessQuery->byAction($request->business_action);
        }
        if ($request->filled('business_days')) {
            $businessQuery->recent($request->business_days);
        } else {
            $businessQuery->recent(30);
        }
        
        $businessLogs = $businessQuery->orderBy('created_at', 'desc')->paginate(25, ['*'], 'business_page');
        
        // Get distinct modules and actions for filters
        try {
            $systemModules = AuditLog::whereIn('action', [
                'login', 'logout', 'create_user', 'update_user', 'delete_user'
            ])->distinct()->pluck('module')->sort();
            
            $systemActions = AuditLog::whereIn('action', [
                'login', 'logout', 'create_user', 'update_user', 'delete_user'
            ])->distinct()->pluck('action')->sort();
            
            $businessModules = AuditLog::whereIn('action', [
                'create_sale', 'receipt_email_sent',
                'create_product', 'update_product', 'delete_product', 'stock_refill',
                'create_category', 'update_category', 'delete_category', 'toggle_category_status'
            ])->distinct()->pluck('module')->sort();
            
            $businessActions = AuditLog::whereIn('action', [
                'create_sale', 'receipt_email_sent',
                'create_product', 'update_product', 'delete_product', 'stock_refill',
                'create_category', 'update_category', 'delete_category', 'toggle_category_status'
            ])->distinct()->pluck('action')->sort();
        } catch (\Exception $e) {
            $systemModules = collect();
            $systemActions = collect();
            $businessModules = collect();
            $businessActions = collect();
        }

        return view('audit.index', compact(
            'systemLogs', 'businessLogs', 'users', 'user',
            'systemModules', 'systemActions', 'businessModules', 'businessActions'
        ));
    }

    public function show(AuditLog $auditLog)
    {
        $user = Auth::guard('admin')->user();

        // Check if user can view this log
        if (!$user->isAdmin() && $auditLog->admin_id !== $user->id) {
            abort(403, 'Unauthorized to view this audit log');
        }

        $auditLog->load('admin');

        return view('audit.show', compact('auditLog'));
    }

    public function export(Request $request)
    {
        $user = Auth::guard('admin')->user();
        
        // Only admins can export all logs
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized to export audit logs');
        }

        $type = $request->get('type', 'system'); // system or business
        $format = $request->get('format', 'csv'); // csv, json, or pdf

        // Build query based on type
        $query = AuditLog::with('admin');

        if ($type === 'system') {
            $query->whereIn('action', [
                'login', 'logout',
                'create_user', 'update_user', 'delete_user'
            ]);
            
            // Apply system filters
            if ($request->filled('system_user_id')) {
                $query->forUser($request->system_user_id);
            }
            if ($request->filled('system_module')) {
                $query->byModule($request->system_module);
            }
            if ($request->filled('system_action')) {
                $query->byAction($request->system_action);
            }
            if ($request->filled('system_days')) {
                $query->recent($request->system_days);
            } else {
                $query->recent(30);
            }
        } else {
            $query->whereIn('action', [
                'create_sale', 'receipt_email_sent',
                'create_product', 'update_product', 'delete_product', 'stock_refill',
                'create_category', 'update_category', 'delete_category', 'toggle_category_status'
            ]);
            
            // Apply business filters
            if ($request->filled('business_user_id')) {
                $query->forUser($request->business_user_id);
            }
            if ($request->filled('business_module')) {
                $query->byModule($request->business_module);
            }
            if ($request->filled('business_action')) {
                $query->byAction($request->business_action);
            }
            if ($request->filled('business_days')) {
                $query->recent($request->business_days);
            } else {
                $query->recent(30);
            }
        }

        $logs = $query->orderBy('created_at', 'desc')->get();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $typeLabel = ucfirst($type);

        try {
            switch ($format) {
                case 'csv':
                    return $this->exportCsv($logs, $typeLabel, $timestamp);
                case 'json':
                    return $this->exportJson($logs, $typeLabel, $timestamp);
                case 'pdf':
                    return $this->exportPdf($logs, $typeLabel, $timestamp);
                default:
                    return $this->exportCsv($logs, $typeLabel, $timestamp);
            }
        } catch (\Exception $e) {
            \Log::error('Export Error', [
                'type' => $type,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            
            // Return error response
            return response()->json([
                'error' => 'Export failed: ' . $e->getMessage(),
                'type' => $type,
                'format' => $format
            ], 500);
        }
    }

    private function exportCsv($logs, $typeLabel, $timestamp)
    {
        $filename = "audit_logs_{$typeLabel}_{$timestamp}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Date/Time',
                'User',
                'Action',
                'Module',
                'Description',
                'IP Address',
                'User Agent'
            ]);

            // CSV data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->admin ? ($log->admin->full_name . ' (' . $log->admin->username . ')') : 'Unknown User',
                    $log->formatted_action,
                    ucfirst($log->module),
                    $log->description,
                    $log->ip_address,
                    $log->user_agent
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportJson($logs, $typeLabel, $timestamp)
    {
        $filename = "audit_logs_{$typeLabel}_{$timestamp}.json";
        
        $data = [
            'export_info' => [
                'type' => $typeLabel,
                'exported_at' => now()->toISOString(),
                'total_records' => $logs->count()
            ],
            'logs' => $logs->map(function($log) {
                return [
                    'id' => $log->id,
                    'date_time' => $log->created_at->toISOString(),
                    'user' => $log->admin ? [
                        'name' => $log->admin->full_name,
                        'username' => $log->admin->username
                    ] : null,
                    'action' => $log->action,
                    'formatted_action' => $log->formatted_action,
                    'module' => $log->module,
                    'description' => $log->description,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent
                ];
            })
        ];

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }

    private function exportPdf($logs, $typeLabel, $timestamp)
    {
        $filename = "audit_logs_{$typeLabel}_{$timestamp}.pdf";
        
        try {
            // Use the existing PDFService
            $pdfService = app(\App\Services\PDFService::class);
            $html = view('audit.export-pdf', compact('logs', 'typeLabel'))->render();
            
            return $pdfService->generatePdfResponse($html, $filename, 'landscape');
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('PDF Export Error: ' . $e->getMessage());
            
            // Fallback to CSV export
            return $this->exportCsv($logs, $typeLabel, $timestamp);
        }
    }
}