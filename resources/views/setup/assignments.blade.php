@extends('layouts.app')

@section('title', 'Setup — Assignments')

@section('content')
@include('setup._nav')
<h1 class="display text-2xl mb-1">Subject Assignments</h1>
<p class="page-lead">For each class, assign which teacher teaches which subject.</p>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="paper-card p-6 h-fit">
        <h2 class="card-title">{{ $editing ? 'Edit assignment' : 'Add assignment' }}</h2>
        <form method="POST"
            action="{{ $editing ? route('setup.assignments.update', $editing) : route('setup.assignments.store') }}"
            class="space-y-4">
            @csrf
            @if ($editing) @method('PUT') @endif

            @error('school_class_id')<p class="err">{{ $message }}</p>@enderror
            @error('subject_id')<p class="err">{{ $message }}</p>@enderror
            @error('teacher_id')<p class="err">{{ $message }}</p>@enderror

            <div>
                <label for="school_class_id" class="lbl">Class</label>
                <x:select name="school_class_id" id="school_class_id"
                    :options="$assignments->pluck('schoolClass.name', 'school_class_id')->unique()"
                    :selected="old('school_class_id', $editing?->school_class_id)" placeholder="Choose a class" required />
            </div>
            <div>
                <label for="subject_id" class="lbl">Subject</label>
                <x:select name="subject_id" id="subject_id" :options="$subjects->pluck('name', 'id')"
                    :selected="old('subject_id', $editing?->subject_id)" placeholder="Choose a subject" required />
            </div>
            <div>
                <label for="teacher_id" class="lbl">Teacher (optional)</label>
                <x:select name="teacher_id" id="teacher_id" :options="$teachers->pluck('name', 'id')"
                    :selected="old('teacher_id', $editing?->teacher_id)" placeholder="Not assigned yet" />
            </div>

            <div class="flex items-center justify-between gap-4">
                @if ($editing)
                    <a href="{{ route('setup.assignments') }}" class="link text-sm">Cancel</a>
                @endif
                <button type="submit" class="btn">
                    {{ $editing ? 'Save changes' : 'Add assignment' }}
                </button>
            </div>
        </form>
    </div>

    <div class="paper-card p-6 overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assignments as $assignment)
                    <tr>
                        <td class="font-semibold">
                            {{ $assignment->schoolClass?->name }}
                            <span class="text-[#7b8fa3]">{{ $assignment->schoolClass?->academicYear?->name }}</span>
                        </td>
                        <td>{{ $assignment->subject?->name }}</td>
                        <td class="text-[#4b6478]">{{ $assignment->teacher?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap">
                            <a class="link" href="{{ route('setup.assignments.edit', $assignment) }}">Edit</a>
                            <form method="POST" action="{{ route('setup.assignments.destroy', $assignment) }}" class="inline"
                                onsubmit="return confirm('Remove this assignment?')">
                                @csrf @method('DELETE')
                                <button class="link link-danger ml-2">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-[#4b6478]">No assignments yet — add the first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
