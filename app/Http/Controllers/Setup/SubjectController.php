<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index()
    {
        return view('setup.subjects', [
            'subjects' => Subject::withCount('assignments')->orderBy('name')->get(),
            'editing' => request('edit') ? Subject::findOrFail(request('edit')) : null,
        ]);
    }

    public function store(Request $request)
    {
        Subject::create($this->validated($request));

        return redirect()->route('setup.subjects')->with('status', __('Subject created.'));
    }

    public function edit(Subject $subject)
    {
        return view('setup.subjects', [
            'subjects' => Subject::withCount('assignments')->orderBy('name')->get(),
            'editing' => $subject,
        ]);
    }

    public function update(Request $request, Subject $subject)
    {
        $subject->update($this->validated($request, $subject));

        return redirect()->route('setup.subjects')->with('status', __('Subject updated.'));
    }

    public function destroy(Subject $subject)
    {
        if ($subject->assignments()->exists()) {
            return back()->withErrors(['code' => __('Cannot delete a subject that is assigned to classes.')]);
        }

        $subject->delete();

        return redirect()->route('setup.subjects')->with('status', __('Subject deleted.'));
    }

    private function validated(Request $request, ?Subject $subject = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', Rule::unique('subjects')->ignore($subject)],
        ]);
    }
}
