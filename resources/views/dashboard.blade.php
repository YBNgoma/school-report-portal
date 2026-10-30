@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="display text-2xl">Hello, {{ auth()->user()->name }}</h1>
        <p class="page-lead mb-0">
            <span class="uppercase tracking-wider text-xs">Roles:</span>
            <span class="font-semibold">{{ auth()->user()->roles->pluck('name')->join(', ') ?: 'none' }}</span>
        </p>
    </div>

    @if (count($cards) > 0)
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($cards as $card)
                <a href="{{ $card['href'] }}" class="paper-card card-link p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#5a7085]">{{ $card['label'] }}</p>
                    <p class="display mt-2 text-3xl">{{ $card['value'] }}</p>
                </a>
            @endforeach
        </div>
    @else
        <div class="paper-card p-4 text-sm text-[#4b6478]">
            Your account has no modules assigned yet. Ask an administrator to check your role.
        </div>
    @endif
</div>
@endsection
