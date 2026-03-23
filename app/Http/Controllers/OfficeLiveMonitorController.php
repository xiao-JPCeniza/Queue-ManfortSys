<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class OfficeLiveMonitorController extends Controller
{
    public function __invoke(Request $request, string $office)
    {
        $officeModel = $request->attributes->get('office') ?? Office::where('slug', $office)->firstOrFail();

        return view('office.hrmo-monitor', ['office' => $officeModel]);
    }
}
