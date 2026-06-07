<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class MenuResolver
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('admin-menu');
    }

    public function resolve(): array
    {
        $cacheEnabled = $this->config['cache']['enabled'] ?? true;
        $cacheTtl = $this->config['cache']['ttl'] ?? 3600;

        if ($cacheEnabled) {
            $cacheKey = $this->config['cache']['key'] ?? 'admin:sidebar:menu';
            $rawGroups = Cache::remember($cacheKey, $cacheTtl, fn () => $this->loadRawGroups());
        } else {
            $rawGroups = $this->loadRawGroups();
        }

        return $this->filterGroups($rawGroups);
    }

    protected function loadRawGroups(): array
    {
        return $this->config['groups'] ?? [];
    }

    protected function filterGroups(array $groups): array
    {
        $resolvedGroups = [];

        foreach ($groups as $group) {
            $items = $this->resolveItems($group['items'] ?? []);

            if (empty($items)) {
                continue;
            }

            $resolvedGroups[] = [
                'heading' => __($group['heading']),
                'collapsible' => $group['collapsible'] ?? false,
                'default_collapsed' => $group['default_collapsed'] ?? false,
                'items' => $items,
            ];
        }

        return $resolvedGroups;
    }

    protected function resolveItems(array $items): array
    {
        $resolved = [];

        foreach ($items as $item) {
            if (!$this->hasPermission($item)) {
                continue;
            }

            $children = [];
            if (isset($item['children']) && is_array($item['children'])) {
                $children = $this->resolveItems($item['children']);
            }

            $resolved[] = [
                'label' => __($item['label']),
                'icon' => $item['icon'] ?? 'question-mark-circle',
                'route' => $item['route'] ?? null,
                'url' => $item['url'] ?? null,
                'permission' => $item['permission'] ?? null,
                'current' => $item['current'] ?? null,
                'target' => $item['target'] ?? '_self',
                'badge' => $this->resolveBadge($item['badge'] ?? null),
                'children' => $children,
                'has_children' => !empty($children),
                'order' => $item['order'] ?? 999,
            ];
        }

        usort($resolved, fn ($a, $b) => $a['order'] <=> $b['order']);

        return $resolved;
    }

    protected function hasPermission(array $item): bool
    {
        $permission = $item['permission'] ?? null;

        if (empty($permission)) {
            return true;
        }

        if (!Auth::check()) {
            return false;
        }

        if (class_exists(\Spatie\Permission\Models\Permission::class)) {
            return Auth::user()->can($permission);
        }

        return Gate::allows($permission);
    }

    protected function resolveBadge(mixed $badge): ?string
    {
        if (is_callable($badge)) {
            return $badge();
        }

        return $badge ? (string) $badge : null;
    }

    public function getMobileConfig(): array
    {
        return $this->config['mobile'] ?? [
            'breakpoint' => 'lg',
            'collapsible' => 'mobile',
            'show_search' => true,
            'search_placeholder' => '搜索菜单...',
        ];
    }

    public static function clearCache(): void
    {
        $config = config('admin-menu');
        $cacheKey = $config['cache']['key'] ?? 'admin:sidebar:menu';
        Cache::forget($cacheKey);
    }
}