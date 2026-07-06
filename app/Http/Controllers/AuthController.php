<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\GenericUser;

class AuthController extends Controller
{
    /**
     * Login with email and password (Mock JWT response to satisfy spec).
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $validated['email'];
        $password = $validated['password'];

        $externalUserService = resolve(\App\Services\ExternalUserService::class);
        $externalUser = $externalUserService->authenticate($email, $password);

        if ($externalUser) {
            $externalId = $externalUser['id'] ?? null;
            if (!$externalId) {
                return response()->json(['message' => 'Invalid user data from external authentication service.'], 401);
            }

            $name = trim(($externalUser['firstname'] ?? '') . ' ' . ($externalUser['lastname'] ?? ''));
            if (empty($name)) {
                $name = $externalUser['name'] ?? $email;
            }

            $role = 'INPUTTER';
            if (isset($externalUser['role'])) {
                $extRole = strtoupper($externalUser['role']);
                if (in_array($extRole, ['AUTHORISER', 'ADMIN', 'AUTHORIZER'])) {
                    $role = 'AUTHORISER';
                }
            }

            $userObj = new GenericUser([
                'id' => $externalId,
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'status' => 'active'
            ]);

            // Cache credentials for 300 seconds
            $cacheKey = 'auth_basic_' . hash('sha256', $email . '|' . $password);
            \Illuminate\Support\Facades\Cache::put($cacheKey, json_encode([
                'id' => $externalId,
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'status' => 'active'
            ]), 300);

            return response()->json([
                'accessToken' => base64_encode($userObj->email . ':' . $password),
                'refreshToken' => 'mock-refresh-token-' . \Illuminate\Support\Str::random(16),
                'role' => $userObj->role,
            ]);
        }

        return response()->json(['message' => 'Invalid email or password.'], 401);
    }

    /**
     * Refresh access token.
     */
    public function refresh()
    {
        return response()->json([
            'accessToken' => 'mock-refreshed-access-token-' . \Illuminate\Support\Str::random(16),
            'refreshToken' => 'mock-refreshed-refresh-token-' . \Illuminate\Support\Str::random(16),
        ]);
    }

    /**
     * Logout current session.
     */
    public function logout()
    {
        return response()->json([
            'message' => 'Logged out successfully.'
        ]);
    }

    /**
     * Get current authenticated user (uses Basic Auth).
     */
    public function me()
    {
        $user = Auth::user();
        if ($user instanceof \Illuminate\Auth\GenericUser) {
            $reflector = new \ReflectionClass($user);
            $property = $reflector->getProperty('attributes');
            $property->setAccessible(true);
            return response()->json($property->getValue($user));
        }
        return response()->json($user);
    }
}
