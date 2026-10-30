@extends('layouts.app')

@section('title', 'Parent portal')

@section('content')
<p class="page-lead">Only published reports appear here.</p>

@if (! $guardian)
    <p class="paper-card p-6 text-sm text-[#5a7085]">Your account is not linked to a guardian record yet. Ask the school to link it.</p>
@else
    <div class="paper-card overflow-x-auto p-6">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Term</th>
                    <th>Average</th>
                    <th>Report</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reports as $report)
                    <tr>
                        <td class="font-medium">{{ $report->student->name }}</td>
                        <td class="text-[#5a7085]">{{ $report->student->schoolClass?->name ?? '—' }}</td>
                        <td>{{ $report->term->name }} ({{ $report->term->academicYear?->name }})</td>
                        <td>{{ $report->average !== null ? number_format((float) $report->average, 2) : '—' }}</td>
                        <td>
                            <a href="{{ route('portal.reports.pdf', $report) }}" class="link">Download PDF</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-[#5a7085]">No published reports yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif
@endsection
