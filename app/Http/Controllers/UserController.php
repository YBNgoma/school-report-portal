<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Subject;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index', [
            'users' => User::with(['roles', 'subjects'])->orderBy('name')->get(),
            'roles' => Roles::ALL,
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', Password::defaults()],
            'role' => ['required', 'string', Rule::in(Roles::ALL)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['role']);

        AuditLog::record('user.created', "Created user {$user->email} as {$data['role']}", $user);

        return redirect()->route('users.index')->with('status', __('User created.'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'string', Rule::in(Roles::ALL)],
        ]);

        // Keep at least one admin, and don't let an admin demote themselves.
        if ($user->is($request->user()) && $data['role'] !== Roles::ADMIN) {
            return back()->withErrors(['role' => __('You cannot remove your own admin role.')]);
        }

        $user->syncRoles([$data['role']]);

        AuditLog::record('user.role_changed', "Set {$user->email} to {$data['role']}", $user);

        return redirect()->route('users.index')->with('status', __('User role updated.'));
    }

    /**
     * Set which subjects a teacher is qualified to teach.
     */
    public function subjects(Request $request, User $user)
    {
        $data = $request->validate([
            'subjects' => ['sometimes', 'array'],
            'subjects.*' => ['integer', 'exists:subjects,id'],
        ]);

        $user->subjects()->sync($data['subjects'] ?? []);

        AuditLog::record('user.subjects_changed', "Updated subjects for {$user->email}", $user);

        return redirect()->route('users.index')->with('status', __('Teacher subjects updated.'));
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => __('You cannot delete your own account.')]);
        }

        AuditLog::record('user.deleted', "Deleted user {$user->email}", $user);

        $user->delete();

        return redirect()->route('users.index')->with('status', __('User deleted.'));
    }
}
