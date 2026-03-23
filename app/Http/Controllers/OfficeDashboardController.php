<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class OfficeDashboardController extends Controller
{
    private const OFFICE_DASHBOARD_SLUGS = [
        'hrmo',
        'business-permits',
        'bplo',
        'mho',
        'mswdo',
        'menro',
        'treasury',
        'accounting',
        'civil-registry',
        'assessors-office',
    ];

    public function __invoke(Request $request, string $office)
    {
        $officeModel = $request->attributes->get('office') ?? Office::where('slug', $office)->firstOrFail();

        if (
            ! ($request->user()?->isSuperAdmin() ?? false)
            && in_array($officeModel->slug, self::OFFICE_DASHBOARD_SLUGS, true)
            && $request->query('tab') === 'queue-management'
        ) {
            return redirect()->route('office.dashboard', ['office' => $officeModel->slug]);
        }

        return view('office.dashboard', ['office' => $officeModel]);
    }
}
