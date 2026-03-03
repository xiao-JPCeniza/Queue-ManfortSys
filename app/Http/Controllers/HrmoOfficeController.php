<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class HrmoOfficeController extends Controller
{
    public function __invoke(Request $request, string $office)
    {
        $officeModel = $request->attributes->get('office') ?? Office::where('slug', $office)->firstOrFail();

        if ($officeModel->slug !== 'hrmo') {
            abort(404, 'HRMO monitor is only available for the HRMO office.');
        }

        return view('office.hrmo-monitor', ['office' => $officeModel]);
    }
}

