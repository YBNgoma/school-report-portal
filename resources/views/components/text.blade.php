@props(['type' => 'text'])

<input {{ $attributes->merge(['type' => $type])->class(['field']) }}>

{{-- Usage: <x:text type="text" name="name" value="{{ old('value') }}" required /> --}}
