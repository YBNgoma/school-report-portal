<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CSV export and import of student records (admission_number, name, date_of_birth).
 */
class StudentTransferController extends Controller
{
    private const HEADERS = ['admission_number', 'name', 'date_of_birth'];

    public function export(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::HEADERS);

            Student::orderBy('name')->each(function (Student $student) use ($handle) {
                fputcsv($handle, [
                    $student->admission_number,
                    $student->name,
                    $student->date_of_birth?->format('Y-m-d'),
                ]);
            });

            fclose($handle);
        }, 'students.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle);

        if ($header === false || array_map('trim', $header) !== self::HEADERS) {
            fclose($handle);

            return back()->withErrors(['file' => __('The first row must be: admission_number, name, date_of_birth.')]);
        }

        $created = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine(self::HEADERS, array_pad(array_map('trim', $row), 3, null));

            $validator = Validator::make($data, [
                'admission_number' => ['required', 'string', 'max:50', 'unique:students,admission_number'],
                'name' => ['required', 'string', 'max:100'],
                'date_of_birth' => ['nullable', 'date', 'before:today'],
            ]);

            if ($validator->fails()) {
                $skipped++;

                continue;
            }

            Student::create([
                'admission_number' => $data['admission_number'],
                'name' => $data['name'],
                'date_of_birth' => $data['date_of_birth'] ?: null,
            ]);
            $created++;
        }

        fclose($handle);

        AuditLog::record('students.imported', "Imported {$created} students, skipped {$skipped}");

        return redirect()->route('students.index')
            ->with('status', __('Imported :created students, skipped :skipped.', ['created' => $created, 'skipped' => $skipped]));
    }
}
