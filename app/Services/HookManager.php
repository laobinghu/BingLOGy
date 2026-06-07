<?php

namespace App\Services;

class HookManager
{
    protected static array $filters = [];
    protected static array $actions = [];

    public static function addFilter(string $name, callable $callback, int $priority = 10): void
    {
        self::$filters[$name][] = ['priority' => $priority, 'callback' => $callback];
        usort(self::$filters[$name], fn ($a, $b) => $b['priority'] <=> $a['priority']);
    }

    public static function applyFilters(string $name, mixed $value, ...$args): mixed
    {
        $callbacks = self::$filters[$name] ?? [];
        $result = $value;
        foreach ($callbacks as $item) {
            $result = ($item['callback'])($result, ...$args);
        }
        return $result;
    }

    public static function addAction(string $name, callable $callback, int $priority = 10): void
    {
        self::$actions[$name][] = ['priority' => $priority, 'callback' => $callback];
        usort(self::$actions[$name], fn ($a, $b) => $b['priority'] <=> $a['priority']);
    }

    public static function doAction(string $name, ...$args): void
    {
        $callbacks = self::$actions[$name] ?? [];
        foreach ($callbacks as $item) {
            ($item['callback'])(...$args);
        }
    }

    public static function getRegistered(string $name = null): array
    {
        if ($name === null) {
            return [
                'filters' => self::$filters,
                'actions' => self::$actions,
            ];
        }
        return [
            'filters' => self::$filters[$name] ?? [],
            'actions' => self::$actions[$name] ?? [],
        ];
    }

    public static function flush(): void
    {
        self::$filters = [];
        self::$actions = [];
    }
}
