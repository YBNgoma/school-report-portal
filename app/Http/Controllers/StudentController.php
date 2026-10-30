<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index()
    {
        return $this->viewWithEditing(null);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $student = Student::create(collect($data)->except('guardian_ids')->all());
        $this->syncGuardians($request, $student, $data);
        AuditLog::record('student.created', "Created student {$student->name} ({$student->admission_number})", $student);

        return redirect()->route('students.index')->with('status', __('Student created.'));
    }

    public function edit(Student $student)
    {
        $this->abortUnlessManageable(request(), $student);

        return $this->viewWithEditing($student);
    }

    public function update(Request $request, Student $student)
    {
        $this->abortUnlessManageable($request, $student);

        $data = $this->validated($request, $student);
        $student->update(collect($data)->except('guardian_ids')->all());
        $this->syncGuardians($request, $student, $data);
        AuditLog::record('student.updated', "Updated student {$student->name} ({$student->admission_number})", $student);

        return redirect()->route('students.index')->with('status', __('Student updated.'));
    }

    public function destroy(Student $student)
    {
        AuditLog::record('student.deleted', "Deleted student {$student->name} ({$student->admission_number})", $student);
        $student->delete();

        return redirect()->route('students.index')->with('status', __('Student deleted.'));
    }

    private function viewWithEditing(?Student $editing)
    {
        $user = request()->user();

        return view('students.index', [
            'students' => $this->studentsFor($user)
                ->with(['schoolClass', 'guardians'])
                ->when(request('class'), fn ($query, $class) => $query->where('school_class_id', $class))
                ->orderBy('name')
                ->get(),
            'classes' => $this->classesFor($user)->with('academicYear')->orderBy('name')->get(),
            'guardians' => $user->can(Permissions::GUARDIANS_MANAGE) ? Guardian::orderBy('name')->get() : collect(),
            'editing' => $editing?->load('guardians'),
        ]);
    }

    private function validated(Request $request, ?Student $student = null): array
    {
        $user = $request->user();

        return $request->validate([
            'admission_number' => ['required', 'string', 'max:50', Rule::unique('students')->ignore($student)],
            'name' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'school_class_id' => [
                $user->hasRole(Roles::ADMIN) ? 'nullable' : 'required',
                Rule::in($this->classesFor($user)->pluck('school_classes.id')->all()),
            ],
            'guardian_ids' => ['nullable', 'array'],
            'guardian_ids.*' => ['integer', 'exists:guardians,id'],
        ]);
    }

    /**
     * Only admins manage guardian links; teachers leave them untouched.
     */
    private function syncGuardians(Request $request, Student $student, array $data): void
    {
        if ($request->user()->can(Permissions::GUARDIANS_MANAGE)) {
            $student->guardians()->sync($data['guardian_ids'] ?? []);
        }
    }

    /**
     * Admins manage every student; teachers only students in their own classes.
     */
    private function abortUnlessManageable(Request $request, Student $student): void
    {
        abort_unless(
            $request->user()->hasRole(Roles::ADMIN)
                || $this->classesFor($request->user())->whereKey($student->school_class_id)->exists(),
            403,
        );
    }

    /**
     * Classes a user may see and place students in. Admins get all classes;
     * teachers get classes they teach or are class teacher of.
     */
    private function classesFor($user)
    {
        $query = SchoolClass::query();

        if (! $user->hasRole(Roles::ADMIN)) {
            $query->where(function ($q) use ($user) {
                $q->where('class_teacher_id', $user->id)
                    ->orWhereHas('assignments', fn ($a) => $a->where('teacher_id', $user->id));
            });
        }

        return $query;
    }

    private function studentsFor($user)
    {
        $query = Student::query();

        if (! $user->hasRole(Roles::ADMIN)) {
            $query->whereIn('school_class_id', $this->classesFor($user)->select('school_classes.id'));
        }

        return $query;
    }
}
