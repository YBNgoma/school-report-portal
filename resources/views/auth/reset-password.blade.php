@extends('layouts.auth')

@section('title', 'Reset Password')
@section('heading', 'Reset Password')
@section('subheading', 'Choose a new password to complete the reset.')

@section('content')
<form method="POST" action="{{ route('password.update') }}" class="space-y-4">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    @error('email')<p class="err">{{ $message }}</p>@enderror
    @error('password')<p class="err">{{ $message }}</p>@enderror

    <div>
        <label for="email" class="lbl">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus autocomplete="email" class="field" />
    </div>

    <div>
        <label for="password" class="lbl">New password</label>
        <input id="password" type="password" name="password" required autocomplete="new-password" class="field" />
    </div>

    <div>
        <label for="password_confirmation" class="lbl">Confirm new password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="field" />
    </div>

    <div class="flex items-center justify-end">
        <button type="submit" class="btn">Reset password</button>
    </div>
</form>
@endsection
