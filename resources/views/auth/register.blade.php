@extends('layouts.auth')

@section('title', 'Register')
@section('heading', 'Create Account')
@section('subheading', 'For guardians and students. Teacher and admin accounts are created by administrators.')

@section('content')
<form method="POST" action="{{ route('register') }}" class="space-y-4">
    @csrf

    @error('name')<p class="err">{{ $message }}</p>@enderror
    @error('email')<p class="err">{{ $message }}</p>@enderror
    @error('role')<p class="err">{{ $message }}</p>@enderror

    <div>
        <label for="name" class="lbl">Full name</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="field" />
    </div>

    <div>
        <label for="email" class="lbl">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="field" />
    </div>

    <div>
        <label for="role" class="lbl">Account type</label>
        <select id="role" name="role" required class="field">
            @foreach ($selfServeRoles as $role)
                <option value="{{ $role }}" @selected(old('role') === $role)>{{ ucfirst($role) }}</option>
            @endforeach
        </select>
    </div>

    @error('password')<p class="err">{{ $message }}</p>@enderror

    <div>
        <label for="password" class="lbl">Password</label>
        <input id="password" type="password" name="password" required autocomplete="new-password" class="field" />
    </div>

    <div>
        <label for="password_confirmation" class="lbl">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="field" />
    </div>

    <div class="flex items-center justify-between gap-4">
        <a href="{{ route('login') }}" class="link text-sm">Already registered?</a>
        <button type="submit" class="btn">Register</button>
    </div>
</form>
@endsection
