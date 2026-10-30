<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Mark;
use App\Models\Term;
use Illuminate\Http\Request;

class MarkController extends Controller
{
    public function index(Request $request)
    {
        $assignment = $request->filled('assignment')
            ? $this->assignableTo($request, (int) $request->query('assignment'))
            : null;

        if ($request->filled('assignment')) {
            abort_unless($assignment, 404);
        }
        $term = $request->filled('term') ? Term::find($request->query('term')) : null;

        $students = $assignment
            ? $assignment->schoolClass->students()->orderBy('name')->get()
            : collect();

        $marks = ($assignment && $term)
            ? Mark::where('assignment_id', $assignment->id)
                ->where('term_id', $term->id)
                ->get()
                ->keyBy('student_id')
            : collect();

        return view('marks.index', [
            'terms' => Term::with('academicYear')->orderBy('id')->get(),
            'assignments' => $this->assignmentsFor($request)->with(['schoolClass.academicYear', 'subject'])->get(),
            'selectedTerm' => $term,
            'selectedAssignment' => $assignment,
            'students' => $students,
            'marks' => $marks,
            'canEnter' => $request->user()->can(\App\Support\Permissions::MARKS_ENTER),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'term_id' => ['required', 'exists:terms,id'],
            'assignment_id' => ['required', 'exists:assignments,id'],
            'scores' => ['present', 'array'],
            'scores.*' => ['nullable', 'numeric', 'between:0,100'],
        ]);

        $assignment = $this->assignableTo($request, (int) $data['assignment_id']);
        abort_unless($assignment, 403);

        $enrolled = $assignment->schoolClass->students()->pluck('id')->all();

        foreach ($data['scores'] as $studentId => $score) {
            if (! in_array((int) $studentId, $enrolled, true)) {
                continue;
            }

            Mark::updateOrCreate(
                [
                    'student_id' => (int) $studentId,
                    'assignment_id' => $assignment->id,
                    'term_id' => $data['term_id'],
                ],
                ['score' => $score === '' ? null : $score],
            );
        }

        return redirect()->route('marks.index', [
            'term' => $data['term_id'],
            'assignment' => $assignment->id,
        ])->with('status', __('Marks saved.'));
    }

    /**
     * Admins can enter marks for any assignment; teachers only for their own.
     */
    private function assignableTo(Request $request, int $assignmentId): ?Assignment
    {
        $query = Assignment::with('schoolClass')->whereKey($assignmentId);

        if (! $request->user()->hasRole(\App\Support\Roles::ADMIN)) {
            $query->where('teacher_id', $request->user()->id);
        }

        return $query->first();
    }

    private function assignmentsFor(Request $request)
    {
        $query = Assignment::query()->orderBy('id');

        if (! $request->user()->hasRole(\App\Support\Roles::ADMIN)) {
            $query->where('teacher_id', $request->user()->id);
        }

        return $query;
    }
}
