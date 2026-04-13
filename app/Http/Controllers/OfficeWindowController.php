<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class OfficeWindowController extends Controller
{
    public function __invoke(Request $request, string $office, int $windowNumber)
    {
        $officeModel = $request->attributes->get('office') ?? Office::where('slug', $office)->firstOrFail();

        abort_if(
            $windowNumber < 1 || $windowNumber > $officeModel->accessibleServiceWindowCount(),
            404,
            'Service window not found.'
        );

        return view('office.window-desk', [
            'office' => $officeModel,
            'windowNumber' => $windowNumber,
        ]);
    }
}
