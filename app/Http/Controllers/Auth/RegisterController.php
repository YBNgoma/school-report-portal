<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Roles a visitor may register for themselves.
     * Admin and teacher accounts are created by administrators.
     *
     * @var list<string>
     */
    private const SELF_SERVE_ROLES = [
        Roles::GUARDIAN,
        Roles::STUDENT,
    ];

    /**
     * Show the registration form.
     */
    public function create(): View
    {
        return view('auth.register', [
            'selfServeRoles' => self::SELF_SERVE_ROLES,
        ]);
    }

    /**
     * Create a new account and assign the chosen self-serve role.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:'.implode(',', self::SELF_SERVE_ROLES)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['role']);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
