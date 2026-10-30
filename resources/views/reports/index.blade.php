@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<p class="page-lead">Compile term reports from the entered marks, then approve and publish them.</p>

<form method="GET" action="{{ route('reports.index') }}" class="paper-card mb-8 grid gap-4 p-6 sm:grid-cols-3">
    <div class="sm:col-span-2">
        <label for="term" class="lbl">Term</label>
        <x:select name="term" id="term" :options="$terms->mapWithKeys(fn ($t) => [$t->id => $t->name.' ('.$t->academicYear?->name.')'])"
            :selected="$selectedTerm?->id" placeholder="Choose a term" />
    </div>
    <div class="flex items-end">
        <button type="submit" class="btn w-full">Load</button>
    </div>
</form>

@if ($selectedTerm)
    @can(\App\Support\Permissions::REPORTS_COMPILE)
        <form method="POST" action="{{ route('reports.compile') }}" class="mb-6">
            @csrf
            <input type="hidden" name="term_id" value="{{ $selectedTerm->id }}">
            <button type="submit" class="btn btn-ghost">Compile reports for {{ $selectedTerm->name }}</button>
        </form>
    @endcan

    @if ($errors->has('status'))
        <p class="err mb-4">{{ $errors->first('status') }}</p>
    @endif

    @foreach ($attendanceClasses as $class)
        <form method="POST" action="{{ route('reports.attendance') }}" class="paper-card mb-6 overflow-x-auto p-6">
            @csrf
            <input type="hidden" name="term_id" value="{{ $selectedTerm->id }}">
            <input type="hidden" name="school_class_id" value="{{ $class->id }}">

            <div class="mb-4 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="card-title">Attendance — {{ $class->name }}</p>
                    <label class="lbl" for="days_total_{{ $class->id }}">School days in term</label>
                    <input type="number" id="days_total_{{ $class->id }}" name="days_total" min="0" max="400" required
                        value="{{ $attendance->first()?->days_total }}" class="field w-28">
                </div>
                <button type="submit" class="btn">Save attendance</button>
            </div>

            <table class="tbl">
                <thead><tr><th>Student</th><th>Days present</th></tr></thead>
                <tbody>
                    @forelse ($class->students as $student)
                        <tr>
                            <td class="font-medium">{{ $student->name }}</td>
                            <td>
                                <input type="number" name="days_present[{{ $student->id }}]" min="0" max="400"
                                    value="{{ $attendance->get($student->id)?->days_present }}" class="field w-28">
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="py-4 text-[#5a7085]">No students in this class yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </form>
    @endforeach

    <div class="paper-card overflow-x-auto p-6">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Average</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reports as $report)
                    <tr>
                        <td class="font-medium">{{ $report->student?->name }}</td>
                        <td class="text-[#5a7085]">{{ $report->student?->schoolClass?->name ?? '—' }}</td>
                        <td>{{ $report->average !== null ? number_format((float) $report->average, 2) : '—' }}</td>
                        <td><span class="badge">{{ $report->status }}</span></td>
                        <td class="whitespace-nowrap">
                            @if ($report->status === \App\Models\Report::COMPILED)
                                @can(\App\Support\Permissions::REPORTS_APPROVE)
                                    <form method="POST" action="{{ route('reports.approve', $report) }}" class="inline">
                                        @csrf
                                        <button class="link">Approve</button>
                                    </form>
                                @endcan
                            @elseif ($report->status === \App\Models\Report::APPROVED)
                                @can(\App\Support\Permissions::REPORTS_PUBLISH)
                                    <form method="POST" action="{{ route('reports.publish', $report) }}" class="inline">
                                        @csrf
                                        <button class="link">Publish</button>
                                    </form>
                                @endcan
                            @endif
                            @unless ($report->status === \App\Models\Report::DRAFT)
                                <a href="{{ route('reports.pdf', $report) }}" class="link ml-3">PDF</a>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-[#5a7085]">No reports yet — compile them for this term.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif
@endsection
