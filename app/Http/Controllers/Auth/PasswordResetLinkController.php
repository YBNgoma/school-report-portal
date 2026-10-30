<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link to the given user.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Always report the same status so the form does not leak
        // whether an account with that email exists.
        $status = Password::sendResetLink($request->only('email'));

        return back()->with(['status' => $status === Password::RESET_LINK_SENT
            ? __($status)
            : __('If an account with that email address exists, a reset link has been sent.')]);
    }
}
