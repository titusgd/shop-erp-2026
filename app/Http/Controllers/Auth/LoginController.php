<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, AuthService $auth): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        try {
            $auth->attempt(
                $request->only('username', 'password'),
                $request->boolean('remember'),
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            RateLimiter::hit($request->throttleKey());

            throw $e;
        }

        RateLimiter::clear($request->throttleKey());

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request, AuthService $auth): RedirectResponse
    {
        $auth->logout();

        return redirect()->route('login');
    }
}
