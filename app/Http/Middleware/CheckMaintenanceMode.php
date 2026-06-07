<?php

namespace App\Http\Middleware;

use App\Services\SettingsManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (SettingsManager::get('maintenance_mode', false)) {
            if (! $request->user()) {
                return response()->view('maintenance');
            }
        }

        return $next($request);
    }
}
