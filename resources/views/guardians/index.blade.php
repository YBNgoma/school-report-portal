@extends('layouts.app')

@section('title', 'Guardians')

@section('content')
<p class="page-lead">Parents and guardians, linked to their students.</p>

<div class="grid gap-8 lg:grid-cols-2">
    <div class="paper-card h-fit p-6">
        <h2 class="card-title">{{ $editing ? 'Edit guardian: ' . $editing->name : 'Add guardian' }}</h2>
        <form method="POST"
            action="{{ $editing ? route('guardians.update', $editing) : route('guardians.store') }}"
            class="space-y-4">
            @csrf
            @if ($editing) @method('PUT') @endif

            @error('name')<p class="err">{{ $message }}</p>@enderror
            @error('email')<p class="err">{{ $message }}</p>@enderror
            @error('phone')<p class="err">{{ $message }}</p>@enderror

            <div>
                <label for="name" class="lbl">Full name</label>
                <x:text name="name" value="{{ old('name', $editing?->name ?? '') }}" required placeholder="e.g. Jane Doe" />
            </div>
            <div>
                <label for="email" class="lbl">Email (optional)</label>
                <x:text name="email" value="{{ old('email', $editing?->email ?? '') }}" placeholder="e.g. jane@example.com" />
            </div>
            <div>
                <label for="phone" class="lbl">Phone (optional)</label>
                <x:text name="phone" value="{{ old('phone', $editing?->phone ?? '') }}" placeholder="e.g. 0700 000000" />
            </div>

            <div class="flex items-center justify-between">
                @if ($editing)
                    <a href="{{ route('guardians.index') }}" class="link">Cancel</a>
                @endif
                <button type="submit" class="btn">{{ $editing ? 'Save changes' : 'Add guardian' }}</button>
            </div>
        </form>
    </div>

    <div class="paper-card overflow-x-auto p-6">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Students</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($guardians as $guardian)
                    <tr>
                        <td class="font-medium">{{ $guardian->name }}</td>
                        <td class="text-[#5a7085]">{{ $guardian->email ?: $guardian->phone ?: '—' }}</td>
                        <td>{{ $guardian->students_count }}</td>
                        <td class="whitespace-nowrap">
                            <a class="link" href="{{ route('guardians.edit', $guardian) }}">Edit</a>
                            <form method="POST" action="{{ route('guardians.destroy', $guardian) }}" class="inline"
                                onsubmit="return confirm('Delete this guardian?')">
                                @csrf @method('DELETE')
                                <button class="link link-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-[#5a7085]">No guardians yet — add the first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
