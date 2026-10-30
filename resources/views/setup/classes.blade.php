@extends('layouts.app')

@section('title', 'Setup — Classes')

@section('content')
@include('setup._nav')
<p class="page-lead">Class arms per academic year (e.g. Grade 7A), each with an assigned class teacher who enrols its students.</p>

<div class="grid gap-8 lg:grid-cols-2">
    <div class="paper-card h-fit p-6">
        <h2 class="card-title">{{ $editing ? 'Edit class: ' . $editing->name : 'Add class' }}</h2>
        <form method="POST"
            action="{{ $editing ? route('setup.classes.update', $editing) : route('setup.classes.store') }}"
            class="space-y-4">
            @csrf
            @if ($editing) @method('PUT') @endif

            @error('name')<p class="err">{{ $message }}</p>@enderror
            @error('academic_year_id')<p class="err">{{ $message }}</p>@enderror
            @error('class_teacher_id')<p class="err">{{ $message }}</p>@enderror

            <div>
                <label for="academic_year_id" class="lbl">Academic year</label>
                <x:select name="academic_year_id" id="academic_year_id" :options="$years->pluck('name', 'id')"
                    :selected="old('academic_year_id', $editing?->academic_year_id)" placeholder="Choose a year" required />
            </div>
            <div>
                <label for="name" class="lbl">Class name</label>
                <x:text name="name" value="{{ old('name', $editing?->name ?? '') }}" required placeholder="e.g. Grade 7A" />
            </div>
            <div>
                <label for="class_teacher_id" class="lbl">Class teacher</label>
                <x:select name="class_teacher_id" id="class_teacher_id" :options="$teachers->pluck('name', 'id')"
                    :selected="old('class_teacher_id', $editing?->class_teacher_id)" placeholder="Choose a class teacher" required />
            </div>

            <div class="flex items-center justify-between">
                @if ($editing)
                    <a href="{{ route('setup.classes') }}" class="link">Cancel</a>
                @endif
                <button type="submit" class="btn">{{ $editing ? 'Save changes' : 'Add class' }}</button>
            </div>
        </form>
    </div>

    <div class="paper-card overflow-x-auto p-6">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Year</th>
                    <th>Class teacher</th>
                    <th>Assignments</th>
                    <th>Students</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($classes as $class)
                    <tr>
                        <td class="font-medium">{{ $class->name }}</td>
                        <td class="text-[#5a7085]">{{ $class->academicYear?->name }}</td>
                        <td class="text-[#5a7085]">{{ $class->classTeacher?->name ?? '—' }}</td>
                        <td>
                            <a class="link" href="{{ route('setup.assignments') }}">{{ $class->assignments_count }}</a>
                        </td>
                        <td>
                            <a class="link" href="{{ route('students.index', ['class' => $class->id]) }}">{{ $class->students_count }}</a>
                        </td>
                        <td class="whitespace-nowrap">
                            <a class="link" href="{{ route('setup.classes.edit', $class) }}">Edit</a>
                            <form method="POST" action="{{ route('setup.classes.destroy', $class) }}" class="inline"
                                onsubmit="return confirm('Delete this class?')">
                                @csrf @method('DELETE')
                                <button class="link link-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-[#5a7085]">No classes yet — add the first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
