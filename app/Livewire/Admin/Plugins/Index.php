<?php

namespace App\Livewire\Admin\Plugins;

use App\Models\PluginState;
use App\Services\PluginManager;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        $discovered = PluginManager::discoverPlugins();

        $states = PluginState::pluck('is_active', 'plugin_name')->toArray();

        $plugins = collect($discovered)->map(function ($item) use ($states) {
            $name = $item['name'];
            $manifest = $item['manifest'];

            return [
                'name' => $name,
                'title' => $manifest['title'] ?? $name,
                'version' => $manifest['version'] ?? '0.0.0',
                'description' => $manifest['description'] ?? '',
                'author' => $manifest['author'] ?? '',
                'is_active' => $states[$name] ?? false,
            ];
        })->sortBy('title')->values()->all();

        return view('livewire.admin.plugins.index', compact('plugins'))
            ->layout('layouts.app', ['title' => '插件管理']);
    }

    public function toggle(string $pluginName): void
    {
        $state = PluginState::where('plugin_name', $pluginName)->first();

        if ($state) {
            $newState = !$state->is_active;
            $state->update(['is_active' => $newState]);

            if ($newState) {
                PluginManager::loadPlugin($pluginName);
            }
        } else {
            PluginState::create([
                'plugin_name' => $pluginName,
                'is_active' => true,
            ]);
            PluginManager::loadPlugin($pluginName);
        }

        session()->flash('success', $newState ? "插件「{$pluginName}」已启用。" : "插件「{$pluginName}」已禁用。");
    }
}
