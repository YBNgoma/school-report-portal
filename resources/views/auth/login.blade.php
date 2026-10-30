@extends('layouts.auth')

@section('title', 'Log In')
@section('heading', 'Welcome')
@section('subheading', 'Log in with your email address and password.')

@section('content')
<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    @error('email')
        <p class="err">{{ $message }}</p>
    @enderror

    <div>
        <label for="email" class="lbl">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="field" />
    </div>

    <div>
        <label for="password" class="lbl">Password</label>
        <input id="password" type="password" name="password" required autocomplete="current-password" class="field" />
    </div>

    <label for="remember" class="flex items-center gap-2 text-sm">
        <input id="remember" type="checkbox" name="remember" class="h-4 w-4 accent-[#0369a1]" />
        Remember me
    </label>

    <div class="flex items-center justify-between gap-4">
        <a href="{{ route('password.request') }}" class="link text-sm">Forgot password?</a>
        <button type="submit" class="btn">Log in</button>
    </div>
</form>

<p class="mt-6 text-sm">
    No account yet?
    <a href="{{ route('register') }}" class="link">Register here</a>
</p>
@endsection
