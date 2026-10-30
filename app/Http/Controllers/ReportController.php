<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Assignment;
use App\Models\Mark;
use App\Models\Report;
use App\Models\ReportTemplate;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Support\Roles;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $term = $request->filled('term') ? Term::with('academicYear')->find($request->query('term')) : null;

        $user = $request->user();

        // Admins record attendance for any class; class teachers for the classes they teach.
        $attendanceClasses = $term
            ? SchoolClass::with('students')
                ->when(! $user->hasRole(Roles::ADMIN), fn ($q) => $q->where('class_teacher_id', $user->id))
                ->orderBy('name')
                ->get()
            : collect();

        return view('reports.index', [
            'terms' => Term::with('academicYear')->orderBy('id')->get(),
            'selectedTerm' => $term,
            'reports' => $term
                ? Report::with('student.schoolClass')->where('term_id', $term->id)
                    ->get()
                    ->sortBy('student.name')
                : collect(),
            'attendanceClasses' => $attendanceClasses,
            'attendance' => $term
                ? Attendance::where('term_id', $term->id)->get()->keyBy('student_id')
                : collect(),
        ]);
    }

    /**
     * Compile (or recompile) a draft report from the student's marks for the term.
     * Approved and published reports are locked.
     */
    public function compile(Request $request)
    {
        $data = $request->validate([
            'term_id' => ['required', 'exists:terms,id'],
        ]);

        $term = Term::findOrFail($data['term_id']);
        $students = Student::all();

        foreach ($students as $student) {
            $existing = Report::where('student_id', $student->id)->where('term_id', $term->id)->first();

            if ($existing && in_array($existing->status, [Report::APPROVED, Report::PUBLISHED], true)) {
                continue;
            }

            $average = Mark::where('student_id', $student->id)
                ->where('term_id', $term->id)
                ->whereNotNull('score')
                ->avg('score');

            Report::updateOrCreate(
                ['student_id' => $student->id, 'term_id' => $term->id],
                [
                    'status' => Report::COMPILED,
                    'average' => $average !== null ? round($average, 2) : null,
                    'compiled_at' => now(),
                ],
            );
        }

        AuditLog::record('report.compiled', "Compiled reports for {$term->name}", $term);

        return redirect()->route('reports.index', ['term' => $term->id])
            ->with('status', __('Reports compiled.'));
    }

    /**
     * Download one report card as PDF, laid out like docs/sample-report.pdf.
     */
    public function pdf(Report $report)
    {
        $report->load(['student.schoolClass.classTeacher', 'term.academicYear']);

        $student = $report->student;
        $class = $student->schoolClass;

        $subjects = $class
            ? Assignment::with(['subject', 'teacher'])
                ->where('school_class_id', $class->id)
                ->get()
                ->sortBy('subject.name')
                ->map(function (Assignment $assignment) use ($student, $report) {
                    $score = Mark::where('student_id', $student->id)
                        ->where('term_id', $report->term_id)
                        ->where('assignment_id', $assignment->id)
                        ->value('score');

                    return [
                        'name' => $assignment->subject->name,
                        'code' => $assignment->subject->code,
                        'teacher' => $assignment->teacher?->name,
                        'score' => $score,
                        'comment' => $score === null ? null : self::commentFor((float) $score),
                    ];
                })
                ->values()
            : collect();

        $pdf = Pdf::loadView('reports.pdf', [
            'report' => $report,
            'student' => $student,
            'class' => $class,
            'term' => $report->term,
            'year' => $report->term->academicYear,
            'subjects' => $subjects,
            'preparedBy' => $class?->classTeacher?->name,
            'attendance' => $student->attendances()->where('term_id', $report->term_id)->first(),
            'sections' => collect(ReportTemplate::current()->sections ?? []),
            'date' => ($report->compiled_at ?? now())->format('j F Y'),
        ])->setPaper('a4');

        return $pdf->download('report-'.$student->admission_number.'-term-'.$report->term_id.'.pdf');
    }

    /**
     * Save attendance (days present / days total) per student for a term.
     * Admins can record it for any class; class teachers only for their own class.
     */
    public function attendance(Request $request)
    {
        $data = $request->validate([
            'term_id' => ['required', 'exists:terms,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'days_total' => ['required', 'integer', 'min:0', 'max:400'],
            'days_present' => ['required', 'array'],
            'days_present.*' => ['nullable', 'integer', 'min:0', 'max:400'],
        ]);

        $class = SchoolClass::findOrFail($data['school_class_id']);
        abort_unless(
            $request->user()->hasRole(Roles::ADMIN) || $class->class_teacher_id === $request->user()->id,
            403,
        );

        $students = $class->students()->pluck('id')->all();

        foreach ($data['days_present'] as $studentId => $present) {
            if (! in_array((int) $studentId, $students, true) || $present === null || $present === '') {
                continue;
            }

            Attendance::updateOrCreate(
                ['student_id' => (int) $studentId, 'term_id' => $data['term_id']],
                [
                    'days_present' => min((int) $present, (int) $data['days_total']),
                    'days_total' => $data['days_total'],
                ],
            );
        }

        AuditLog::record('attendance.saved', "Saved attendance for {$class->name}", $class);

        return redirect()->route('reports.index', ['term' => $data['term_id']])
            ->with('status', __('Attendance saved.'));
    }

    /**
     * Short remark for a mark, matching the bands in the sample report.
     */
    public static function commentFor(float $score): string
    {
        return match (true) {
            $score >= 85 => 'Outstanding',
            $score >= 75 => 'Very Good',
            $score >= 60 => 'Good',
            default => 'Needs Improvement',
        };
    }

    public function approve(Report $report)
    {
        if ($report->status !== Report::COMPILED) {
            return back()->withErrors(['status' => __('Only compiled reports can be approved.')]);
        }

        $report->update(['status' => Report::APPROVED, 'approved_at' => now()]);
        AuditLog::record('report.approved', "Approved report for student {$report->student_id}", $report);

        return back()->with('status', __('Report approved.'));
    }

    public function publish(Report $report)
    {
        if ($report->status !== Report::APPROVED) {
            return back()->withErrors(['status' => __('Only approved reports can be published.')]);
        }

        $report->update(['status' => Report::PUBLISHED, 'published_at' => now()]);
        AuditLog::record('report.published', "Published report for student {$report->student_id}", $report);

        return back()->with('status', __('Report published.'));
    }
}
