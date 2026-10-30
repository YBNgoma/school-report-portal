@extends('layouts.app')

@section('title', 'Mark entry')

@section('content')
<p class="page-lead">Choose a term and one of your subject assignments, then enter each student's score (0–100).</p>

<form method="GET" action="{{ route('marks.index') }}" class="paper-card mb-8 grid gap-4 p-6 sm:grid-cols-3">
    <div>
        <label for="term" class="lbl">Term</label>
        <x:select name="term" id="term" :options="$terms->mapWithKeys(fn ($t) => [$t->id => $t->name.' ('.$t->academicYear?->name.')'])"
            :selected="$selectedTerm?->id" placeholder="Choose a term" />
    </div>
    <div>
        <label for="assignment" class="lbl">Class &amp; subject</label>
        <x:select name="assignment" id="assignment"
            :options="$assignments->mapWithKeys(fn ($a) => [$a->id => $a->schoolClass?->name.' — '.$a->subject?->name])"
            :selected="$selectedAssignment?->id" placeholder="Choose an assignment" />
    </div>
    <div class="flex items-end">
        <button type="submit" class="btn w-full">Load</button>
    </div>
</form>

@if ($selectedTerm && $selectedAssignment)
    <div class="paper-card overflow-x-auto p-6">
        <form method="POST" action="{{ route('marks.store') }}">
            @csrf
            <input type="hidden" name="term_id" value="{{ $selectedTerm->id }}">
            <input type="hidden" name="assignment_id" value="{{ $selectedAssignment->id }}">

            @error('scores.*')<p class="err mb-4">{{ $message }}</p>@enderror

            <table class="tbl">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Admission no.</th>
                        <th>Score (0–100)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td class="font-medium">{{ $student->name }}</td>
                            <td class="text-[#5a7085]">{{ $student->admission_number }}</td>
                            <td>
                                <input type="number" name="scores[{{ $student->id }}]" min="0" max="100" step="0.01"
                                    value="{{ old('scores.'.$student->id, $marks->get($student->id)?->score) }}"
                                    @disabled(! $canEnter)
                                    class="field w-28">
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-[#5a7085]">No students are enrolled in this class yet.</td></tr>
                    @endforelse
                </tbody>
            </table>

            @if ($canEnter && $students->isNotEmpty())
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn">Save marks</button>
                </div>
            @endif
        </form>
    </div>
@elseif ($assignments->isEmpty())
    <p class="text-sm text-[#5a7085]">You have no subject assignments yet.</p>
@endif
@endsection
