@extends('layouts.auth')

@section('title', 'Forgot Password')
@section('heading', 'Forgot Password')
@section('subheading', 'Enter your email address and we will send you a password reset link.')

@section('content')
<form method="POST" action="{{ route('password.email') }}" class="space-y-4">
    @csrf

    @error('email')<p class="err">{{ $message }}</p>@enderror

    <div>
        <label for="email" class="lbl">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="field" />
    </div>

    <div class="flex items-center justify-end">
        <button type="submit" class="btn">Send reset link</button>
    </div>
</form>
@endsection
