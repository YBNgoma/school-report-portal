<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report — {{ $student->name }}</title>
    <style>
        @page { margin: 32px 40px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1b1b18; }
        table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .school { font-size: 20px; font-weight: bold; letter-spacing: 1px; }
        .tagline { font-size: 10px; color: #5a7085; font-style: italic; text-align: right; }
        .title { text-align: center; font-size: 13px; font-weight: bold; margin: 18px 0 10px; text-transform: uppercase; }
        .label { font-size: 9px; color: #5a7085; text-transform: uppercase; letter-spacing: .5px; }
        .value { font-size: 12px; font-weight: bold; padding-bottom: 8px; }
        .section { font-size: 11px; font-weight: bold; text-transform: uppercase; margin: 14px 0 6px; border-bottom: 1px solid #e3e3e0; padding-bottom: 4px; }
        .marks td { padding: 6px 4px; border-bottom: 1px solid #f0f0ee; }
        .marks th { text-align: left; font-size: 9px; color: #5a7085; text-transform: uppercase; padding: 4px; border-bottom: 1px solid #dbdbd7; }
        .subject { font-size: 11px; font-weight: bold; }
        .teacher { font-size: 8px; color: #5a7085; text-transform: uppercase; }
        .score { font-weight: bold; font-size: 12px; }
        .remarks td { vertical-align: top; padding: 4px 8px 4px 0; }
        .footer { text-align: center; font-size: 9px; color: #5a7085; margin-top: 24px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td class="school">THE VICTORY COLLEGE</td>
            <td class="tagline">Forge Your Future</td>
        </tr>
    </table>

    <div class="title">{{ $term->name }} {{ $year->name }} — Progress Report</div>

    @if ($sections->contains('student_details'))
    <table>
        <tr>
            <td style="width: 34%">
                <div class="label">Student name</div>
                <div class="value">{{ $student->name }}</div>
            </td>
            <td style="width: 22%">
                <div class="label">Class</div>
                <div class="value">{{ $class?->name ?? '—' }}</div>
            </td>
            <td style="width: 22%">
                <div class="label">Date</div>
                <div class="value">{{ $date }}</div>
            </td>
            @if ($sections->contains('prepared_by'))
            <td style="width: 22%">
                <div class="label">Prepared by</div>
                <div class="value">{{ $preparedBy ?? '—' }}</div>
            </td>
            @endif
        </tr>
    </table>
    @endif

    @if ($sections->contains('attendance'))
    <div class="section">Attendance</div>
    <table>
        <tr>
            <td style="width: 50%">
                <div class="label">Days present</div>
                <div class="value">{{ $attendance?->days_present ?? '—' }}</div>
            </td>
            <td style="width: 50%">
                <div class="label">Days in term</div>
                <div class="value">{{ $attendance?->days_total ?? '—' }}</div>
            </td>
        </tr>
    </table>
    @endif

    @if ($sections->contains('academic_performance'))
    <div class="section">Academic performance</div>

    <table class="marks">
        <thead>
            <tr>
                <th style="width: 55%">Subject</th>
                <th style="width: 15%">Mark (%)</th>
                <th style="width: 30%">Comment</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($subjects as $subject)
                <tr>
                    <td>
                        <div class="subject">{{ $subject['name'] }}</div>
                        <div class="teacher">{{ strtoupper($subject['code']) }}@if ($subject['teacher']) · {{ $subject['teacher'] }}@endif</div>
                    </td>
                    <td class="score">{{ $subject['score'] !== null ? number_format((float) $subject['score'], 0).'%' : '—' }}</td>
                    <td>{{ $subject['comment'] ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No subjects are assigned to this class.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @endif

    <div class="footer">
        A Zimbabwean school with international standards.<br>
        Parklands, Bulawayo, Zimbabwe | +263 71 414 9156 | admissions@victorycollege.ac.zw<br>
        https://thevictorycollegezw.com
    </div>
</body>
</html>
