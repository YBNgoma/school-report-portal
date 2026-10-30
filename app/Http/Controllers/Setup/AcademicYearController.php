<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicYearController extends Controller
{
    /**
     * The setup landing page lists all years.
     */
    public function index()
    {
        return view('setup.years', [
            'years' => AcademicYear::withCount('terms')->withCount('classes')->orderBy('name')->get(),
            'editing' => request('edit') ? AcademicYear::findOrFail(request('edit')) : null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $year = AcademicYear::create($data);
        $this->setActiveYear($data['is_active'] ?? false, $year->id);

        return redirect()->route('setup.years')->with('status', __('Academic year created.'));
    }

    public function edit(AcademicYear $year)
    {
        return view('setup.years', [
            'years' => AcademicYear::withCount('terms')->withCount('classes')->orderBy('name')->get(),
            'editing' => $year,
        ]);
    }

    public function update(Request $request, AcademicYear $year)
    {
        $data = $this->validated($request, $year);

        $year->update($data);
        $this->setActiveYear($data['is_active'] ?? false, $year->id);

        return redirect()->route('setup.years')->with('status', __('Academic year updated.'));
    }

    public function destroy(AcademicYear $year)
    {
        if ($year->classes()->exists()) {
            return back()->withErrors(['name' => __('Cannot delete a year that still has classes.')]);
        }

        $year->delete();

        return redirect()->route('setup.years')->with('status', __('Academic year deleted.'));
    }

    private function validated(Request $request, ?AcademicYear $year = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('academic_years')->ignore($year)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    /**
     * Only one year may be active at a time.
     */
    private function setActiveYear(bool $isAlsoActive, ?int $activeId = null): void
    {
        if ($isAlsoActive) {
            AcademicYear::where('is_active', true)
                ->when($activeId, fn ($q) => $q->where('id', '!=', $activeId))
                ->update(['is_active' => false]);
        }
    }
}
