<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Guardian;
use App\Models\Student;
use App\Support\Permissions;
use Illuminate\Http\Request;

/**
 * Role-aware home page: counts and links only for modules the user can open.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $cards = [];

        if ($user->can(Permissions::SETUP_MANAGE)) {
            $cards[] = [
                'label' => 'Active academic year',
                'value' => AcademicYear::where('is_active', true)->value('name') ?? 'None set',
                'href' => route('setup.index'),
            ];
        }

        if ($user->can(Permissions::STUDENTS_MANAGE)) {
            $cards[] = [
                'label' => 'Students',
                'value' => Student::count(),
                'href' => route('students.index'),
            ];
        }

        if ($user->can(Permissions::GUARDIANS_MANAGE)) {
            $cards[] = [
                'label' => 'Guardians',
                'value' => Guardian::count(),
                'href' => route('guardians.index'),
            ];
        }

        if ($user->can(Permissions::MARKS_ENTER)) {
            $cards[] = [
                'label' => 'Your assignments',
                'value' => Assignment::where('teacher_id', $user->id)->count(),
                'href' => route('marks.index'),
            ];
        }

        if ($user->can(Permissions::PORTAL_REPORTS_VIEW)) {
            $guardian = Guardian::where('user_id', $user->id)->first();
            $cards[] = [
                'label' => 'Linked children',
                'value' => $guardian ? $guardian->students()->count() : 0,
                'href' => route('portal.index'),
            ];
        }

        return view('dashboard', [
            'cards' => $cards,
        ]);
    }
}
