<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\WebAuthnKey;
use App\Services\WebAuthnService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WebAuthnController extends Controller
{
    protected WebAuthnService $webAuthnService;

    public function __construct(WebAuthnService $webAuthnService)
    {
        $this->webAuthnService = $webAuthnService;
    }

    /**
     * Show WebAuthn management page.
     */
    public function manage()
    {
        $user = Auth::guard('admin')->user();
        $webAuthnKeys = $user->webAuthnKeys()->orderBy('created_at', 'desc')->get();
        
        return view('auth.webauthn-manage', compact('webAuthnKeys'));
    }

    /**
     * Generate registration options for a new WebAuthn key.
     */
    public function registerOptions(Request $request): JsonResponse
    {
        try {
            $user = Auth::guard('admin')->user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $options = $this->webAuthnService->generateRegistrationOptions($user);
            
            return response()->json($options);
        } catch (\Exception $e) {
            Log::error('WebAuthn registration options error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate registration options'], 500);
        }
    }

    /**
     * Register a new WebAuthn key.
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $user = Auth::guard('admin')->user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $credential = $request->json()->all();
            $deviceName = $request->input('device_name', 'WebAuthn Key');
            
            $webAuthnKey = $this->webAuthnService->verifyAndStoreCredential($user, $credential, $deviceName);
            
            return response()->json([
                'success' => true,
                'message' => 'WebAuthn key registered successfully',
                'key' => [
                    'id' => $webAuthnKey->id,
                    'name' => $webAuthnKey->name,
                    'created_at' => $webAuthnKey->created_at->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('WebAuthn registration error: ' . $e->getMessage());
            return response()->json(['error' => 'Registration failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate authentication options for WebAuthn login.
     */
    public function loginOptions(Request $request): JsonResponse
    {
        try {
            $username = $request->input('username');
            $user = null;
            
            if ($username) {
                $user = Admin::where('username', $username)->first();
            }
            
            $options = $this->webAuthnService->generateAuthenticationOptions($user);
            
            return response()->json($options);
        } catch (\Exception $e) {
            Log::error('WebAuthn login options error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate login options'], 500);
        }
    }

    /**
     * Authenticate using WebAuthn.
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $assertion = $request->json()->all();
            
            $user = $this->webAuthnService->verifyAssertion($assertion);
            
            if (!$user) {
                return response()->json(['error' => 'Authentication failed'], 401);
            }
            
            Auth::guard('admin')->login($user);
            
            return response()->json([
                'success' => true,
                'message' => 'Authentication successful',
                'redirect' => route('dashboard')
            ]);
        } catch (\Exception $e) {
            Log::error('WebAuthn login error: ' . $e->getMessage());
            return response()->json(['error' => 'Authentication failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a WebAuthn key.
     */
    public function deleteKey(Request $request, WebAuthnKey $key): JsonResponse
    {
        try {
            $user = Auth::guard('admin')->user();
            
            if ($key->authenticatable_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            $key->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'WebAuthn key deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('WebAuthn key deletion error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete key'], 500);
        }
    }

    /**
     * Update a WebAuthn key name.
     */
    public function updateKeyName(Request $request, WebAuthnKey $key): JsonResponse
    {
        try {
            $user = Auth::guard('admin')->user();
            
            if ($key->authenticatable_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            $request->validate([
                'name' => 'required|string|max:255'
            ]);
            
            $key->update(['name' => $request->input('name')]);
            
            return response()->json([
                'success' => true,
                'message' => 'WebAuthn key name updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('WebAuthn key update error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update key name'], 500);
        }
    }
}