<?php

namespace App\Http\Controllers;

use App\Services\InstallationManager;
use Illuminate\Http\JsonResponse;

class InstallHealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(InstallationManager::status());
    }
}
