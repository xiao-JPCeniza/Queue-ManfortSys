<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class BploOfficeController extends Controller
{
    public function __invoke(Request $request, string $office)
    {
        $officeModel = $request->attributes->get('office') ?? Office::where('slug', $office)->firstOrFail();

        if (!in_array($officeModel->slug, ['business-permits', 'bplo'], true)) {
            abort(404, 'BPLO monitor is only available for the Business Permits office.');
        }

        return view('office.bplo-monitor', ['office' => $officeModel]);
    }
}
