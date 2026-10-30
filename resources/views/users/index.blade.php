@extends('layouts.app')

@section('title', 'Users')

@section('content')
<style>
    @media (max-width: 639px) {
        .users-tbl thead { display: none; }
        .users-tbl, .users-tbl tbody, .users-tbl tr, .users-tbl td { display: block; width: 100%; }
        .users-tbl tr { border-top: 1px solid #e6eef5; padding: 0.75rem 0; }
        .users-tbl td { border: 0; padding: 0.25rem 0; }
        .users-tbl td[data-label]::before { content: attr(data-label); display: block; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #5a7085; }
    }
</style>

<p class="page-lead">Staff, guardian and student accounts, and the role each one holds.</p>

<div class="grid gap-8 lg:grid-cols-2">
    <div class="paper-card h-fit p-6">
        <h2 class="card-title">Add user</h2>
        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf

            @error('name')<p class="err">{{ $message }}</p>@enderror
            @error('email')<p class="err">{{ $message }}</p>@enderror
            @error('password')<p class="err">{{ $message }}</p>@enderror
            @error('role')<p class="err">{{ $message }}</p>@enderror

            <div>
                <label for="name" class="lbl">Full name</label>
                <x:text name="name" value="{{ old('name') }}" required placeholder="e.g. Jane Doe" />
            </div>
            <div>
                <label for="email" class="lbl">Email</label>
                <x:text type="email" name="email" value="{{ old('email') }}" required placeholder="e.g. jane@school.test" />
            </div>
            <div>
                <label for="password" class="lbl">Temporary password</label>
                <x:text type="password" name="password" required />
            </div>
            <div>
                <label for="role" class="lbl">Role</label>
                <x:select name="role" :options="collect($roles)->mapWithKeys(fn ($r) => [$r => ucfirst($r)])->all()" :selected="old('role', 'teacher')" />
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn">Add user</button>
            </div>
        </form>
    </div>

    <div class="paper-card overflow-x-auto p-6">
        @error('user')<p class="err mb-3">{{ $message }}</p>@enderror
        @error('role')<p class="err mb-3">{{ $message }}</p>@enderror
        <table class="tbl users-tbl">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="font-medium" data-label="Name">{{ $user->name }}</td>
                        <td class="text-[#5a7085]" data-label="Email">{{ $user->email }}</td>
                        <td data-label="Role">
                            <form method="POST" action="{{ route('users.update', $user) }}" class="flex flex-wrap items-center gap-2">
                                @csrf @method('PUT')
                                <x:select name="role" class="!w-auto" :options="collect($roles)->mapWithKeys(fn ($r) => [$r => ucfirst($r)])->all()" :selected="$user->roles->first()?->name" />
                                <button class="link">Save</button>
                            </form>
                            @if ($user->hasRole('teacher'))
                                <form method="POST" action="{{ route('users.subjects', $user) }}" class="mt-2 space-y-1">
                                    @csrf @method('PUT')
                                    <p class="text-xs font-semibold uppercase tracking-wide text-[#5a7085]">Teaches</p>
                                    @foreach ($subjects as $subject)
                                        <label class="flex items-center gap-2 text-sm">
                                            <input type="checkbox" name="subjects[]" value="{{ $subject->id }}"
                                                @checked($user->subjects->contains($subject->id))>
                                            {{ $subject->name }}
                                        </label>
                                    @endforeach
                                    <button class="link">Save subjects</button>
                                </form>
                            @endif
                        </td>
                        <td class="whitespace-nowrap" data-label="Actions">
                            @if (! $user->is(auth()->user()))
                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline"
                                    onsubmit="return confirm('Delete this user?')">
                                    @csrf @method('DELETE')
                                    <button class="link link-danger">Delete</button>
                                </form>
                            @else
                                <span class="text-[#5a7085]">You</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-[#5a7085]">No users yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
