<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use Illuminate\Http\Request;

class GuardianController extends Controller
{
    public function index()
    {
        return $this->viewWithEditing(null);
    }

    public function store(Request $request)
    {
        Guardian::create($this->validated($request));

        return redirect()->route('guardians.index')->with('status', __('Guardian created.'));
    }

    public function edit(Guardian $guardian)
    {
        return $this->viewWithEditing($guardian);
    }

    public function update(Request $request, Guardian $guardian)
    {
        $guardian->update($this->validated($request));

        return redirect()->route('guardians.index')->with('status', __('Guardian updated.'));
    }

    public function destroy(Guardian $guardian)
    {
        $guardian->delete();

        return redirect()->route('guardians.index')->with('status', __('Guardian deleted.'));
    }

    private function viewWithEditing(?Guardian $editing)
    {
        return view('guardians.index', [
            'guardians' => Guardian::withCount('students')->orderBy('name')->get(),
            'editing' => $editing,
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);
    }
}
