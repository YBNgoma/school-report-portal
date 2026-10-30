@extends('layouts.app')

@section('title', 'Students')

@section('content')
<p class="page-lead">Enrolled students, their class and linked guardians.</p>

<div class="grid gap-8 lg:grid-cols-2">
    <div class="paper-card h-fit p-6">
        <h2 class="card-title">{{ $editing ? 'Edit student: ' . $editing->name : 'Add student' }}</h2>
        <form method="POST"
            action="{{ $editing ? route('students.update', $editing) : route('students.store') }}"
            class="space-y-4">
            @csrf
            @if ($editing) @method('PUT') @endif

            @error('admission_number')<p class="err">{{ $message }}</p>@enderror
            @error('name')<p class="err">{{ $message }}</p>@enderror
            @error('date_of_birth')<p class="err">{{ $message }}</p>@enderror
            @error('school_class_id')<p class="err">{{ $message }}</p>@enderror
            @error('guardian_ids')<p class="err">{{ $message }}</p>@enderror

            <div>
                <label for="admission_number" class="lbl">Admission number</label>
                <x:text name="admission_number" value="{{ old('admission_number', $editing?->admission_number ?? '') }}" required placeholder="e.g. ADM-0001" />
            </div>
            <div>
                <label for="name" class="lbl">Full name</label>
                <x:text name="name" value="{{ old('name', $editing?->name ?? '') }}" required placeholder="e.g. John Doe" />
            </div>
            <div>
                <label for="date_of_birth" class="lbl">Date of birth (optional)</label>
                <x:text name="date_of_birth" type="date" value="{{ old('date_of_birth', $editing?->date_of_birth?->format('Y-m-d') ?? '') }}" />
            </div>
            <div>
                <label for="school_class_id" class="lbl">Class (optional)</label>
                <x:select name="school_class_id" id="school_class_id"
                    :options="$classes->mapWithKeys(fn ($c) => [$c->id => $c->name.' ('.$c->academicYear?->name.')'])"
                    :selected="old('school_class_id', $editing?->school_class_id)" placeholder="Not assigned to a class" />
            </div>
            @can(\App\Support\Permissions::GUARDIANS_MANAGE)
                <div>
                    <label for="guardian_ids" class="lbl">Guardians (hold Ctrl/Cmd to select several)</label>
                    <select name="guardian_ids[]" id="guardian_ids" multiple size="4" class="field">
                        @php($selectedGuardians = old('guardian_ids', $editing?->guardians->pluck('id')->all() ?? []))
                        @foreach ($guardians as $guardian)
                            <option value="{{ $guardian->id }}" @selected(in_array($guardian->id, $selectedGuardians))>{{ $guardian->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endcan

            <div class="flex items-center justify-between">
                @if ($editing)
                    <a href="{{ route('students.index') }}" class="link">Cancel</a>
                @endif
                <button type="submit" class="btn">{{ $editing ? 'Save changes' : 'Add student' }}</button>
            </div>
        </form>
    </div>

    <div class="paper-card overflow-x-auto p-6">
        @can(\App\Support\Permissions::IMPORT_EXPORT)
            <div class="mb-4 flex flex-wrap items-center justify-between gap-4 text-sm">
                <a href="{{ route('students.export') }}" class="link">Export CSV</a>
                <form method="POST" action="{{ route('students.import') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <input type="file" name="file" accept=".csv,text/csv" required class="field">
                    <button type="submit" class="btn btn-ghost whitespace-nowrap">Import CSV</button>
                </form>
            </div>
            @error('file')<p class="err mb-4">{{ $message }}</p>@enderror
        @endcan

        @if (request('class'))
            <p class="mb-4 text-sm"><a class="link" href="{{ route('students.index') }}">Show all students</a></p>
        @endif

        <table class="tbl">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Guardians</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    <tr>
                        <td>
                            <span class="font-medium">{{ $student->name }}</span>
                            <span class="block text-xs text-[#5a7085]">{{ $student->admission_number }}</span>
                        </td>
                        <td class="text-[#5a7085]">{{ $student->schoolClass?->name ?? '—' }}</td>
                        <td class="text-[#5a7085]">{{ $student->guardians->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="whitespace-nowrap">
                            <a class="link" href="{{ route('students.edit', $student) }}">Edit</a>
                            @can(\App\Support\Permissions::STUDENTS_MANAGE)
                                <form method="POST" action="{{ route('students.destroy', $student) }}" class="inline"
                                    onsubmit="return confirm('Delete this student?')">
                                    @csrf @method('DELETE')
                                    <button class="link link-danger">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-[#5a7085]">No students yet — add the first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
