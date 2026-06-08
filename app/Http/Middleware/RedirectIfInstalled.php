<?php

namespace App\Http\Middleware;

use App\Services\InstallationManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (InstallationManager::isInstalled()) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
