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

        if ($office->slug === 'hrmo') {
            $user = $request->user();
            $isAssignedHrmoOfficeAdmin = $user->isOfficeAdmin() && $user->office_id === $office->id;

            if (!$user->isSuperAdmin() && !$isAssignedHrmoOfficeAdmin) {
                abort(403, 'The HRMO dashboard is only accessible to Super Admin and the assigned HRMO Office Admin.');
            }
        }

        if (!$request->user()->canAccessOffice($office)) {
            abort(403, 'You do not have access to this office.');
        }

        $request->attributes->set('office', $office);

        return $next($request);
    }
}
