<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * @param  array{username: string, password: string, remember?: bool}  $credentials
     *
     * @throws ValidationException
     */
    public function attempt(array $credentials, bool $remember = false): void
    {
        if (! Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ], $remember)) {
            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        request()->session()->regenerate();
    }

    public function logout(): void
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
