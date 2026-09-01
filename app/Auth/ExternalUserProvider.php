<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Services\ExternalUserService;
use Illuminate\Auth\GenericUser;

class ExternalUserProvider implements UserProvider
{
    protected $userService;

    public function __construct(ExternalUserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        $user = $this->userService->getUserById((int) $identifier);

        if (!$user) {
            return null;
        }

        $name = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
        if (empty($name)) {
            $name = $user['name'] ?? 'External User';
        }

        $role = 'INPUTTER';
        if (isset($user['role'])) {
            $extRole = '';
            $roleId = null;
            if (is_array($user['role'])) {
                $extRole = $user['role']['name'] ?? '';
                $roleId = $user['role']['id'] ?? null;
            } else {
                $extRole = $user['role'];
                $roleId = $user['role'];
            }
            
            $roleIdAttr = $user['role_id'] ?? null;
            $extRoleUpper = strtoupper($extRole);
            
            if ($roleId == 67 || $roleIdAttr == 67 || in_array($extRoleUpper, ['AUTHORISER', 'ADMIN', 'AUTHORIZER']) || str_contains($extRoleUpper, 'ADMIN') || str_contains($extRoleUpper, 'AUTHORIS') || str_contains($extRoleUpper, 'AUTHORIZ')) {
                $role = 'AUTHORISER';
            }
        }

        return new GenericUser([
            'id' => $user['id'],
            'name' => $name,
            'email' => $user['email'] ?? null,
            'role' => $role,
            'status' => $user['status'] ?? 'active',
        ]);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        //
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        return null;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return false;
    }

    /**
     * Rehash the user's password if required.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @param  bool  $force
     * @return void
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        //
    }
}
