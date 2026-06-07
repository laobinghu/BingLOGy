<?php

namespace App\Livewire\Admin\StorageStrategies;

use App\Models\StorageStrategy;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.admin.layout')]
class Index extends Component
{
    public function render()
    {
        $strategies = StorageStrategy::orderBy('key')->get();

        return view('livewire.admin.storage-strategies.index', compact('strategies'));
    }

    public function toggle(string $key): void
    {
        $strategy = StorageStrategy::where('key', $key)->firstOrFail();

        if ($strategy->is_default) {
            session()->flash('error', '默认存储策略不可禁用。');
            return;
        }

        $strategy->update(['is_active' => !$strategy->is_active]);

        session()->flash('success', $strategy->is_active ? "「{$strategy->label}」已启用。" : "「{$strategy->label}」已禁用。");
    }

    public function setDefault(string $key): void
    {
        $strategy = StorageStrategy::where('key', $key)->firstOrFail();

        StorageStrategy::where('is_default', true)->update(['is_default' => false]);
        $strategy->update(['is_default' => true]);

        session()->flash('success', "「{$strategy->label}」已设为默认存储。");
    }

    public function delete(string $key): void
    {
        $strategy = StorageStrategy::where('key', $key)->firstOrFail();

        if ($strategy->is_default) {
            session()->flash('error', '默认存储策略不可删除，请先设置其他为默认。');
            return;
        }

        if ($strategy->uploadPolicies()->exists()) {
            session()->flash('error', '该存储策略正被上传策略使用，无法删除。');
            return;
        }

        $strategy->delete();

        session()->flash('success', "「{$strategy->label}」已删除。");
    }
}
