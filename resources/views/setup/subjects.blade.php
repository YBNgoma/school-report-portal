@extends('layouts.app')

@section('title', 'Setup — Subjects')

@section('content')
@include('setup._nav')
<p class="page-lead">Taught subjects with a short code used on the report cards.</p>

<div class="grid gap-8 lg:grid-cols-2">
    <div class="paper-card h-fit p-6">
        <h2 class="card-title">{{ $editing ? 'Edit subject: ' . $editing->name : 'Add subject' }}</h2>
        <form method="POST"
            action="{{ $editing ? route('setup.subjects.update', $editing) : route('setup.subjects.store') }}"
            class="space-y-4">
            @csrf
            @if ($editing) @method('PUT') @endif

            @error('name')<p class="err">{{ $message }}</p>@enderror
            @error('code')<p class="err">{{ $message }}</p>@enderror

            <div>
                <label for="name" class="lbl">Subject name</label>
                <x:text name="name" value="{{ old('name', $editing?->name ?? '') }}" required placeholder="e.g. Mathematics" />
            </div>
            <div>
                <label for="code" class="lbl">Code</label>
                <x:text name="code" value="{{ old('code', $editing?->code ?? '') }}" required placeholder="e.g. MATH" />
            </div>

            <div class="flex items-center justify-between">
                @if ($editing)
                    <a href="{{ route('setup.subjects') }}" class="link">Cancel</a>
                @endif
                <button type="submit" class="btn">{{ $editing ? 'Save changes' : 'Add subject' }}</button>
            </div>
        </form>
    </div>

    <div class="paper-card overflow-x-auto p-6">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Code</th>
                    <th>Assignments</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subjects as $subject)
                    <tr>
                        <td class="font-medium">{{ $subject->name }}</td>
                        <td class="text-[#5a7085]">{{ $subject->code }}</td>
                        <td>{{ $subject->assignments_count }}</td>
                        <td class="whitespace-nowrap">
                            <a class="link" href="{{ route('setup.subjects.edit', $subject) }}">Edit</a>
                            <form method="POST" action="{{ route('setup.subjects.destroy', $subject) }}" class="inline"
                                onsubmit="return confirm('Delete this subject?')">
                                @csrf @method('DELETE')
                                <button class="link link-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-[#5a7085]">No subjects yet — add the first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
