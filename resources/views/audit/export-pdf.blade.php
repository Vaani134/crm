<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $typeLabel }} Activity Audit Logs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }
        .badge-primary { background-color: #007bff; }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-danger { background-color: #dc3545; }
        .badge-info { background-color: #17a2b8; }
        .badge-dark { background-color: #343a40; }
        .badge-secondary { background-color: #6c757d; }
        .text-small {
            font-size: 10px;
            color: #666;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $typeLabel }} Activity Audit Logs</h1>
        <p>Inventory & Sales Management System</p>
        <p>Generated on: {{ now()->format('F d, Y \a\t H:i:s') }}</p>
    </div>

    <div class="summary">
        <strong>Export Summary:</strong><br>
        Total Records: {{ $logs->count() }}<br>
        Activity Type: {{ $typeLabel }} Activities<br>
        Date Range: {{ $logs->isNotEmpty() ? $logs->last()->created_at->format('M d, Y') . ' to ' . $logs->first()->created_at->format('M d, Y') : 'No records' }}
    </div>

    @if($logs->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Date/Time</th>
                <th style="width: 15%;">User</th>
                <th style="width: 12%;">Action</th>
                <th style="width: 10%;">Module</th>
                <th style="width: 35%;">Description</th>
                <th style="width: 13%;">IP Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>
                    <strong>{{ $log->created_at->format('M d, Y') }}</strong><br>
                    <span class="text-small">{{ $log->created_at->format('H:i:s') }}</span>
                </td>
                <td>
                    <strong>{{ $log->admin->full_name }}</strong><br>
                    <span class="text-small">{{ $log->admin->username }}</span>
                </td>
                <td>
                    <span class="badge badge-{{ $log->badge_color === 'light' ? 'dark' : $log->badge_color }}">
                        {{ $log->formatted_action }}
                    </span>
                </td>
                <td>
                    {{ ucfirst($log->module) }}
                </td>
                <td>
                    {{ $log->description }}
                </td>
                <td class="text-small">
                    {{ $log->ip_address }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align: center; padding: 50px; color: #666;">
        <h3>No Records Found</h3>
        <p>No {{ strtolower($typeLabel) }} activity logs match the current filters.</p>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the Inventory & Sales Management System.</p>
        <p>For questions or support, please contact your system administrator.</p>
    </div>
</body>
</html>