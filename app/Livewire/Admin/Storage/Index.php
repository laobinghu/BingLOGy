<?php

namespace App\Livewire\Admin\Storage;

use App\Models\StorageDisk;
use App\Services\StorageManager;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        $disks = StorageDisk::query()->orderBy('name')->get();

        return view('livewire.admin.storage.index', compact('disks'))
            ->layout('layouts.app', ['title' => '存储管理']);
    }

    public function setDefault(int $id): void
    {
        StorageDisk::query()->update(['is_default' => false]);
        StorageDisk::where('id', $id)->update(['is_default' => true]);

        session()->flash('success', '默认存储已更新。');
    }

    public function toggleAvailability(int $id): void
    {
        $disk = StorageDisk::findOrFail($id);
        $disk->update(['is_available' => !$disk->is_available]);

        session()->flash('success', $disk->is_available ? '存储已启用。' : '存储已禁用。');
    }

    public function testConnection(int $id): void
    {
        $disk = StorageDisk::findOrFail($id);

        try {
            StorageManager::registerDisk($disk);

            $adapter = StorageManager::disk($disk->name);
            $adapter->put('_test_binglogy.txt', 'connection test');
            $adapter->delete('_test_binglogy.txt');

            session()->flash('success', "「{$disk->name}」连接测试成功。");
        } catch (\Throwable $e) {
            session()->flash('error', "连接测试失败：{$e->getMessage()}");
        }
    }

    public function delete(int $id): void
    {
        $disk = StorageDisk::findOrFail($id);

        if ($disk->is_default) {
            session()->flash('error', '无法删除默认存储，请先设置其他存储为默认。');
            return;
        }

        $disk->delete();

        session()->flash('success', "「{$disk->name}」已删除。");
    }
}
