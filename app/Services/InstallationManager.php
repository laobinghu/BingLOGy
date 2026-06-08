<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InstallationManager
{
    public const FLAG_FILE = 'storage/app/installed.lock';
    public const TOKEN_FILE = 'storage/app/install.token';
    public const DRAFT_FILE = 'storage/app/install.draft.json';

    public static function isInstalled(): bool
    {
        return File::exists(base_path(self::FLAG_FILE));
    }

    public static function markInstalled(array $payload = []): void
    {
        $directory = dirname(base_path(self::FLAG_FILE));

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $content = [
            'installed_at' => now()->toIso8601String(),
            'app_name' => config('app.name'),
            'payload' => Arr::except($payload, ['password']),
        ];

        File::put(base_path(self::FLAG_FILE), json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        self::clearInstallToken();
    }

    public static function unmarkInstalled(): void
    {
        if (File::exists(base_path(self::FLAG_FILE))) {
            File::delete(base_path(self::FLAG_FILE));
        }

        self::clearInstallDraft();
    }

    public static function installedPayload(): array
    {
        if (! File::exists(base_path(self::FLAG_FILE))) {
            return [];
        }

        $decoded = json_decode((string) File::get(base_path(self::FLAG_FILE)), true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function ensureInstallToken(): string
    {
        $path = base_path(self::TOKEN_FILE);

        if (File::exists($path)) {
            return trim((string) File::get($path));
        }

        $token = Str::random(64);
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $token);

        return $token;
    }

    public static function verifyInstallToken(?string $token): bool
    {
        $stored = trim((string) @File::get(base_path(self::TOKEN_FILE)));

        return $stored !== '' && is_string($token) && hash_equals($stored, $token);
    }

    public static function clearInstallToken(): void
    {
        if (File::exists(base_path(self::TOKEN_FILE))) {
            File::delete(base_path(self::TOKEN_FILE));
        }
    }

    public static function loadInstallDraft(): array
    {
        $path = base_path(self::DRAFT_FILE);

        if (! File::exists($path)) {
            return ['step' => 'intro', 'data' => []];
        }

        $decoded = json_decode((string) File::get($path), true);

        if (! is_array($decoded)) {
            return ['step' => 'intro', 'data' => []];
        }

        return [
            'step' => is_string($decoded['step'] ?? null) ? $decoded['step'] : 'intro',
            'data' => is_array($decoded['data'] ?? null) ? $decoded['data'] : [],
        ];
    }

    public static function saveInstallDraft(array $data, string $step): array
    {
        $draft = self::loadInstallDraft();
        $merged = array_merge($draft['data'], $data);
        $content = [
            'step' => $step,
            'data' => $merged,
            'updated_at' => now()->toIso8601String(),
        ];

        File::ensureDirectoryExists(dirname(base_path(self::DRAFT_FILE)));
        File::put(base_path(self::DRAFT_FILE), json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $content;
    }

    public static function clearInstallDraft(): void
    {
        if (File::exists(base_path(self::DRAFT_FILE))) {
            File::delete(base_path(self::DRAFT_FILE));
        }
    }

    public static function status(): array
    {
        $database = self::databaseStatus();
        $checks = [
            'runtime' => [
                'php_version' => [
                    'label' => 'PHP 8.4+',
                    'value' => PHP_VERSION,
                    'passed' => version_compare(PHP_VERSION, '8.4.0', '>='),
                ],
                'app_key' => [
                    'label' => 'APP_KEY',
                    'value' => filled((string) config('app.key')) ? 'present' : 'missing',
                    'passed' => filled((string) config('app.key')),
                ],
                'database_driver' => [
                    'label' => 'Default database',
                    'value' => (string) config('database.default'),
                    'passed' => filled((string) config('database.default')),
                ],
            ],
            'extensions' => [
                'pdo_sqlite' => [
                    'label' => 'pdo_sqlite',
                    'passed' => extension_loaded('pdo_sqlite'),
                ],
                'pdo_mysql' => [
                    'label' => 'pdo_mysql',
                    'passed' => extension_loaded('pdo_mysql'),
                ],
                'mbstring' => [
                    'label' => 'mbstring',
                    'passed' => extension_loaded('mbstring'),
                ],
                'openssl' => [
                    'label' => 'openssl',
                    'passed' => extension_loaded('openssl'),
                ],
                'curl' => [
                    'label' => 'curl',
                    'passed' => extension_loaded('curl'),
                ],
                'intl' => [
                    'label' => 'intl',
                    'passed' => extension_loaded('intl'),
                ],
                'fileinfo' => [
                    'label' => 'fileinfo',
                    'passed' => extension_loaded('fileinfo'),
                ],
            ],
            'filesystem' => [
                'storage' => [
                    'label' => 'storage/',
                    'passed' => is_writable(storage_path()),
                ],
                'bootstrap_cache' => [
                    'label' => 'bootstrap/cache/',
                    'passed' => is_writable(base_path('bootstrap/cache')),
                ],
                'env' => [
                    'label' => '.env or .env.example',
                    'passed' => is_writable(base_path('.env')) || is_writable(base_path('.env.example')),
                ],
            ],
            'database' => [
                'connection' => [
                    'label' => 'Connection',
                    'value' => $database['driver'] ?? config('database.default'),
                    'passed' => $database['connected'] ?? false,
                    'message' => $database['message'] ?? null,
                ],
            ],
        ];

        return [
            'installed' => self::isInstalled(),
            'checks' => $checks,
            'ready' => $checks['runtime']['php_version']['passed']
                && self::groupPassed($checks['extensions'])
                && self::groupPassed($checks['filesystem'])
                && ($checks['database']['connection']['passed'] ?? false),
        ];
    }

    public static function databaseStatus(): array
    {
        try {
            return [
                'connected' => true,
                'driver' => DB::connection()->getDriverName(),
            ];
        } catch (\Throwable $e) {
            return [
                'connected' => false,
                'driver' => config('database.default'),
                'message' => $e->getMessage(),
            ];
        }
    }

    public static function installationToken(): string
    {
        return Str::random(40);
    }

    protected static function groupPassed(array $group): bool
    {
        foreach ($group as $check) {
            if (! (($check['passed'] ?? false) === true)) {
                return false;
            }
        }

        return true;
    }
}
