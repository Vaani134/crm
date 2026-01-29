<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        $admins = Admin::orderBy('full_name')->get();
        return view('admin.index', compact('admins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3|unique:admins,username',
            'password' => 'required|string|min:6',
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|unique:admins,email',
            'role' => 'required|in:admin,employee'
        ]);

        $admin = Admin::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
            'email' => $request->email,
            'role' => $request->role
        ]);

        // Log user creation
        AuditLogService::logUserCreate($admin->toArray());

        return back()->with('success', 'User created successfully');
    }

    public function edit(Admin $admin)
    {
        $admins = Admin::orderBy('full_name')->get();
        return view('admin.index', compact('admins', 'admin'));
    }

    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'username' => 'required|string|min:3|unique:admins,username,' . $admin->id,
            'password' => 'nullable|string|min:6',
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'role' => 'required|in:admin,employee'
        ]);

        $oldData = $admin->toArray();

        $updateData = [
            'username' => $request->username,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'role' => $request->role
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $admin->update($updateData);

        // Log user update
        AuditLogService::logUserUpdate($oldData, $admin->fresh()->toArray());

        return redirect()->route('admin.index')->with('success', 'User updated successfully');
    }

    public function destroy(Admin $admin)
    {
        // Prevent deleting current user
        if ($admin->id === Auth::guard('admin')->id()) {
            return back()->withErrors(['error' => 'Cannot delete your own account']);
        }

        $adminData = $admin->toArray();
        $admin->delete();

        // Log user deletion
        AuditLogService::logUserDelete($adminData);

        return back()->with('success', 'User deleted successfully');
    }
}