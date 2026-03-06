<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\View\View;

class SuperAdminQueueReportsController extends Controller
{
    public function __invoke(): View
    {
        $officeModel = Office::where('slug', 'hrmo')->firstOrFail();

        return view('super-admin.queue-reports', ['office' => $officeModel]);
    }
}

