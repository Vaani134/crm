<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Services\AuditLogService;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $admin = Admin::where('username', $request->username)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            Auth::guard('admin')->login($admin);
            
            // Log successful login
            AuditLogService::logLogin($admin->id, $admin->username);
            
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['error' => 'Invalid username or password']);
    }

    public function logout()
    {
        $user = Auth::guard('admin')->user();
        
        // Log logout before actually logging out
        if ($user) {
            AuditLogService::logLogout($user->id, $user->username);
        }
        
        Auth::guard('admin')->logout();
        return redirect()->route('login');
    }
}