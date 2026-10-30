<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    public function index()
    {
        return view('setup.assignments', [
            'assignments' => Assignment::with(['schoolClass.academicYear', 'subject', 'teacher'])
                ->join('school_classes', 'assignments.school_class_id', '=', 'school_classes.id')
                ->orderBy('school_classes.name')
                ->select('assignments.*')
                ->get(),
            'years' => AcademicYear::orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'teachers' => User::role('teacher')->orderBy('name')->get(),
            'editing' => request('edit') ? Assignment::findOrFail(request('edit')) : null,
        ]);
    }

    public function store(Request $request)
    {
        Assignment::create($this->validated($request));

        return redirect()->route('setup.assignments')->with('status', __('Assignment created.'));
    }

    public function edit(Assignment $assignment)
    {
        return $this->viewWithEditing($assignment);
    }

    public function update(Request $request, Assignment $assignment)
    {
        $assignment->update($this->validated($request, $assignment));

        return redirect()->route('setup.assignments')->with('status', __('Assignment updated.'));
    }

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();

        return redirect()->route('setup.assignments')->with('status', __('Assignment deleted.'));
    }

    private function viewWithEditing(?Assignment $editing)
    {
        return view('setup.assignments', [
            'assignments' => Assignment::with(['schoolClass.academicYear', 'subject', 'teacher'])
                ->join('school_classes', 'assignments.school_class_id', '=', 'school_classes.id')
                ->orderBy('school_classes.name')
                ->select('assignments.*')
                ->get(),
            'years' => AcademicYear::orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'teachers' => User::role('teacher')->orderBy('name')->get(),
            'editing' => $editing,
        ]);
    }

    private function validated(Request $request, ?Assignment $assignment = null): array
    {
        $validator = Validator::make($request->all(), [
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['nullable', 'exists:users,id'],
        ]);

        $validator->after(function ($validator) use ($request, $assignment) {
            $class = SchoolClass::find($request->integer('school_class_id'));

            if ($class && $class->assignments()
                ->where('subject_id', $request->integer('subject_id'))
                ->when($assignment, fn ($q) => $q->where('id', '!=', $assignment->id))
                ->exists()) {
                $validator->errors()->add('subject_id', __('This subject is already assigned to this class.'));
            }

            // A teacher can only be assigned to a subject they are qualified to teach.
            if ($request->filled('teacher_id') && $request->filled('subject_id')) {
                $qualified = Subject::find($request->integer('subject_id'))
                    ?->teachers()->whereKey($request->integer('teacher_id'))->exists();

                if (! $qualified) {
                    $validator->errors()->add('teacher_id', __('This teacher is not qualified to teach this subject. Add it on their user profile first.'));
                }
            }
        });

        return $validator->validate();
    }
}
