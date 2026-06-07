<?php

namespace App\Services;

use App\Models\StorageDisk;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class StorageManager
{
    public static function registerDisks(): void
    {
        try {
            $disks = StorageDisk::where('is_available', true)->get();
        } catch (\Throwable $e) {
            return;
        }

        foreach ($disks as $disk) {
            try {
                static::registerDisk($disk);
            } catch (\Throwable $e) {
                logger()->warning("StorageManager: failed to register disk [{$disk->name}]: {$e->getMessage()}");
            }
        }
    }

    public static function registerDisk(StorageDisk $disk): void
    {
        $config = $disk->config;

        $diskConfig = match ($disk->driver) {
            'local' => [
                'driver' => 'local',
                'root' => $config['path'] ?? storage_path('app/public'),
            ],
            'oss' => throw new \InvalidArgumentException('OSS driver is not implemented yet.'),
            'cos' => throw new \InvalidArgumentException('COS driver is not implemented yet.'),
            's3' => throw new \InvalidArgumentException('S3 driver is not implemented yet.'),
            default => throw new \InvalidArgumentException("Unsupported driver: {$disk->driver}"),
        };

        config(["filesystems.disks.{$disk->name}" => $diskConfig]);
    }

    public static function disk(?string $name = null): FilesystemAdapter
    {
        return Storage::disk($name ?? config('filesystems.default'));
    }

    public static function defaultDisk(): FilesystemAdapter
    {
        $default = StorageDisk::where('is_default', true)->first();

        return Storage::disk($default ? $default->name : config('filesystems.default'));
    }

    public static function availableDisks(): array
    {
        return StorageDisk::where('is_available', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
