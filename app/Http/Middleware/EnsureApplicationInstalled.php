<?php

namespace App\Http\Middleware;

use App\Services\InstallationManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicationInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! InstallationManager::isInstalled() && ! $request->is('install*')) {
            return redirect()->route('install.index');
        }

        return $next($request);
    }
}
