<?php

namespace App\Http\Middleware;

use App\Models\Office;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOfficeAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $officeSlug = $request->route('office');
        $office = Office::where('slug', $officeSlug)->first();

        if (!$office) {
            abort(404, 'Office not found.');
        }

        if (!$request->user()->canAccessOffice($office)) {
            abort(403, 'You do not have access to this office.');
        }

        $request->attributes->set('office', $office);

        return $next($request);
    }
}
