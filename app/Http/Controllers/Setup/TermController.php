<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TermController extends Controller
{
    /**
     * List the terms of one academic year with an add/edit form.
     */
    public function index(AcademicYear $year)
    {
        return view('setup.terms', [
            'year' => $year,
            'terms' => $year->terms()->orderBy('name')->get(),
            'editing' => request('edit') ? Term::findOrFail(request('edit')) : null,
        ]);
    }

    public function store(Request $request, AcademicYear $year)
    {
        $year->terms()->create($this->validated($request, $year));

        return redirect()->route('setup.year.terms', $year)->with('status', __('Term created.'));
    }

    public function update(Request $request, AcademicYear $year, Term $term)
    {
        abort_if($term->academic_year_id !== $year->id, 404);

        $term->update($this->validated($request, $year, $term));

        return redirect()->route('setup.year.terms', $year)->with('status', __('Term updated.'));
    }

    public function destroy(AcademicYear $year, Term $term)
    {
        abort_if($term->academic_year_id !== $year->id, 404);

        $term->delete();

        return redirect()->route('setup.year.terms', $year)->with('status', __('Term deleted.'));
    }

    private function validated(Request $request, AcademicYear $year, ?Term $term = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('terms')->where('academic_year_id', $year->id)->ignore($term),
            ],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
    }
}
