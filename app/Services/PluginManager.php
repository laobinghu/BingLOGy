<?php

namespace App\Services;

use App\Models\PluginState;
use Illuminate\Support\Facades\Log;

class PluginManager
{
    public static function loadActivePlugins(): void
    {
        try {
            $states = PluginState::query()->where('is_active', true)->get();
            foreach ($states as $state) {
                self::loadPlugin($state->plugin_name);
            }
        } catch (\Throwable $e) {
            Log::warning('PluginManager::loadActivePlugins failed: ' . $e->getMessage());
        }
    }

    public static function loadPlugin(string $pluginName): bool
    {
        $paths = [
            base_path("plugins/{$pluginName}"),
            app_path("Plugins/{$pluginName}"),
        ];

        foreach ($paths as $base) {
            $provider = "{$base}/ServiceProvider.php";
            if (file_exists($provider)) {
                require_once $provider;
                $class = self::guessProviderClass($pluginName);
                if (class_exists($class)) {
                    try {
                        (new $class())->register();
                        return true;
                    } catch (\Throwable $e) {
                        Log::warning("Plugin [{$pluginName}] register failed: " . $e->getMessage());
                    }
                }
            }
        }
        return false;
    }

    public static function guessProviderClass(string $pluginName): string
    {
        $studly = str_replace('-', '_', $pluginName);
        $studlyParts = explode('_', $studly);
        $studlyName = implode('', array_map('ucfirst', $studlyParts));

        return "Plugins\\{$studlyName}\\ServiceProvider";
    }

    public static function discoverPlugins(): array
    {
        $results = [];
        $searchPaths = [
            base_path('plugins'),
            app_path('Plugins'),
        ];

        foreach ($searchPaths as $basePath) {
            if (! is_dir($basePath)) {
                continue;
            }
            foreach (new \DirectoryIterator($basePath) as $item) {
                if ($item->isDir() && ! $item->isDot()) {
                    $name = $item->getFilename();
                    $manifestPath = $item->getPathname() . '/plugin.json';
                    $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
                    $results[] = [
                        'name' => $name,
                        'manifest' => $manifest,
                    ];
                }
            }
        }

        return $results;
    }

    public static function setActive(string $pluginName, bool $active): void
    {
        PluginState::updateOrCreate(
            ['plugin_name' => $pluginName],
            ['is_active' => $active]
        );
    }
}
