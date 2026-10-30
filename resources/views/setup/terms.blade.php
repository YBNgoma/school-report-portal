@extends('layouts.app')

@section('title', 'Setup — Terms (' . $year->name . ')')

@section('content')
@include('setup._nav')
<h1 class="display text-2xl mb-1">Terms — {{ $year->name }}</h1>
<p class="page-lead">
    <a href="{{ route('setup.index') }}" class="link">Academic years</a>
    › Terms of {{ $year->name }}
</p>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="paper-card p-6 h-fit">
        <h2 class="card-title">{{ $editing ? 'Edit term: ' . $editing->name : 'Add term' }}</h2>
        <form method="POST"
            action="{{ $editing ? route('setup.year.terms.update', [$year, $editing]) : route('setup.year.terms.store', $year) }}"
            class="space-y-4">
            @csrf
            @if ($editing) @method('PUT') @endif

            @error('name')<p class="err">{{ $message }}</p>@enderror
            @error('end_date')<p class="err">{{ $message }}</p>@enderror

            <div>
                <label for="name" class="lbl">Name</label>
                <x:text name="name" value="{{ old('name', $editing?->name ?? '') }}" required placeholder="e.g. Term 1" />
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

            <div class="flex items-center justify-between gap-4">
                @if ($editing)
                    <a href="{{ route('setup.year.terms', $year) }}" class="link text-sm">Cancel</a>
                @endif
                <button type="submit" class="btn">
                    {{ $editing ? 'Save changes' : 'Add term' }}
                </button>
            </div>
        </form>
    </div>

    <div class="paper-card p-6 overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Term</th>
                    <th>Period</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($terms as $term)
                    <tr>
                        <td class="font-semibold">{{ $term->name }}</td>
                        <td class="whitespace-nowrap text-[#4b6478]">
                            {{ $term->start_date?->format('Y-m-d') ?? '—' }}<br>{{ $term->end_date?->format('Y-m-d') ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap">
                            <a class="link" href="{{ route('setup.year.terms', ['year' => $year, 'edit' => $term->id]) }}">Edit</a>
                            <form method="POST" action="{{ route('setup.year.terms.destroy', [$year, $term]) }}" class="inline"
                                onsubmit="return confirm('Delete this term?')">
                                @csrf @method('DELETE')
                                <button class="link link-danger ml-2">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-[#4b6478]">No terms yet — add the first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
