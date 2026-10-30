<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ReportTemplate;
use Illuminate\Http\Request;

class ReportTemplateController extends Controller
{
    public function edit()
    {
        return view('settings.report-template', [
            'template' => ReportTemplate::current(),
            'sections' => ReportTemplate::SECTIONS,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'sections' => ['present', 'array'],
            'sections.*' => ['string', 'in:'.implode(',', array_keys(ReportTemplate::SECTIONS))],
        ]);

        $template = ReportTemplate::current();
        $template->update(['sections' => array_values(array_unique($data['sections']))]);
        AuditLog::record('report-template.updated', 'Updated report card sections', $template);

        return redirect()->route('settings.report-template')->with('status', __('Report template saved.'));
    }
}
