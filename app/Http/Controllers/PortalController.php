<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Report;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Parent portal: guardians see published reports for their linked children.
 */
class PortalController extends Controller
{
    public function index(Request $request)
    {
        $guardian = Guardian::where('user_id', $request->user()->id)->first();

        $reports = $guardian
            ? Report::with(['student.schoolClass', 'term.academicYear'])
                ->where('status', Report::PUBLISHED)
                ->whereIn('student_id', $guardian->students()->select('students.id'))
                ->get()
                ->sortBy([['student.name', 'asc'], ['term.id', 'desc']])
            : collect();

        return view('portal.index', [
            'guardian' => $guardian,
            'reports' => $reports,
        ]);
    }

    /**
     * Download a published report, only for a guardian linked to its student.
     */
    public function pdf(Request $request, Report $report)
    {
        $guardian = Guardian::where('user_id', $request->user()->id)->first();

        $linked = $guardian
            && $guardian->students()->where('students.id', $report->student_id)->exists();

        if (! $linked || $report->status !== Report::PUBLISHED) {
            throw new NotFoundHttpException;
        }

        return app(ReportController::class)->pdf($report);
    }
}
