@extends('layouts.app')

@section('title', 'Setup — Academic Years')

@section('content')
@include('setup._nav')
<h1 class="display text-2xl mb-1">Academic Years</h1>
<p class="page-lead">Years are the top level of the school calendar. Terms live inside a year.</p>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="paper-card p-6 h-fit">
        <h2 class="card-title">{{ $editing ? 'Edit year: ' . $editing->name : 'Add year' }}</h2>
        <form method="POST"
            action="{{ $editing ? route('setup.years.update', $editing) : route('setup.years.store') }}"
            class="space-y-4">
            @csrf
            @if ($editing) @method('PUT') @endif

            @error('name')<p class="err">{{ $message }}</p>@enderror
            @error('end_date')<p class="err">{{ $message }}</p>@enderror

            <div>
                <label for="name" class="lbl">Name</label>
                <x:text name="name" value="{{ old('name', $editing?->name ?? '') }}" required placeholder="e.g. 2026/2027" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="lbl">Start date</label>
                    <x:text type="date" name="start_date" value="{{ old('start_date', $editing?->start_date?->format('Y-m-d')) }}" />
                </div>
                <div>
                    <label for="end_date" class="lbl">End date</label>
                    <x:text type="date" name="end_date" value="{{ old('end_date', $editing?->end_date?->format('Y-m-d')) }}" />
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="h-4 w-4 accent-[#0369a1]" @checked(old('is_active', $editing?->is_active ?? false))>
                Set as the active year
            </label>

            <div class="flex items-center justify-between gap-4">
                @if ($editing)
                    <a href="{{ route('setup.index') }}" class="link text-sm">Cancel</a>
                @endif
                <button type="submit" class="btn">
                    {{ $editing ? 'Save changes' : 'Add year' }}
                </button>
            </div>
        </form>
    </div>

    <div class="paper-card p-6 overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Year</th>
                    <th>Period</th>
                    <th>Terms</th>
                    <th>Classes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($years as $year)
                    <tr>
                        <td class="font-semibold">
                            {{ $year->name }}
                            @if ($year->is_active)
                                <span class="badge ml-1">active</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-[#4b6478]">
                            {{ $year->start_date?->format('Y-m-d') ?? '—' }}<br>{{ $year->end_date?->format('Y-m-d') ?? '—' }}
                        </td>
                        <td><a class="link" href="{{ route('setup.year.terms', $year) }}">{{ $year->terms_count }}</a></td>
                        <td>{{ $year->classes_count }}</td>
                        <td class="whitespace-nowrap">
                            <a class="link" href="{{ route('setup.years.edit', $year) }}">Edit</a>
                            <form method="POST" action="{{ route('setup.years.destroy', $year) }}" class="inline"
                                onsubmit="return confirm('Delete this year?')">
                                @csrf @method('DELETE')
                                <button class="link link-danger ml-2">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-[#4b6478]">No years yet — add the first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
