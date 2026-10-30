<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class ClassController extends Controller
{
    public function index()
    {
        return view('setup.classes', [
            'classes' => SchoolClass::with(['academicYear', 'classTeacher'])->withCount(['assignments', 'students'])->orderBy('name')->get(),
            'years' => AcademicYear::orderBy('name')->get(),
            'teachers' => User::role('teacher')->orderBy('name')->get(),
            'editing' => request('edit') ? SchoolClass::findOrFail(request('edit')) : null,
        ]);
    }

    public function store(Request $request)
    {
        SchoolClass::create($this->validated($request));

        return redirect()->route('setup.classes')->with('status', __('Class created.'));
    }

    public function edit(SchoolClass $class)
    {
        return $this->indexWithEditing($class);
    }

    public function update(Request $request, SchoolClass $class)
    {
        $class->update($this->validated($request, $class));

        return redirect()->route('setup.classes')->with('status', __('Class updated.'));
    }

    public function destroy(SchoolClass $class)
    {
        if ($class->assignments()->exists()) {
            return back()->withErrors(['name' => __('Cannot delete a class that has subject assignments.')]);
        }

        $class->delete();

        return redirect()->route('setup.classes')->with('status', __('Class deleted.'));
    }

    private function indexWithEditing(?SchoolClass $editing)
    {
        return view('setup.classes', [
            'classes' => SchoolClass::with(['academicYear', 'classTeacher'])->withCount(['assignments', 'students'])->orderBy('name')->get(),
            'years' => AcademicYear::orderBy('name')->get(),
            'teachers' => User::role('teacher')->orderBy('name')->get(),
            'editing' => $editing,
        ]);
    }

    private function validated(Request $request, ?SchoolClass $class = null): array
    {
        return $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('school_classes')->where('academic_year_id', $request->integer('academic_year_id'))->ignore($class),
            ],
            'class_teacher_id' => [
                'required',
                Rule::exists('model_has_roles', 'model_id')->where(fn ($q) => $q
                    ->where('model_type', User::class)
                    ->where('role_id', Role::findByName(Roles::TEACHER, config('auth.defaults.guard'))->id)),
            ],
        ], [
            'class_teacher_id.required' => __('Every class needs an assigned class teacher.'),
            'class_teacher_id.exists' => __('The class teacher must be a user with the teacher role.'),
        ]);
    }
}
