<?php

namespace App\Services;

use App\Models\StorageDisk;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class StorageManager
{
    public static function registerDisks(): void
    {
        $disks = StorageDisk::where('is_available', true)->get();

        foreach ($disks as $disk) {
            static::registerDisk($disk);
        }
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

    public static function registerDisk(StorageDisk $disk): void
    {
        $config = $disk->config;

        $diskConfig = match ($disk->driver) {
            'local' => [
                'driver' => 'local',
                'root' => $config['path'] ?? storage_path('app/public'),
            ],
            'oss' => [
                'driver' => 'oss',
                'access_key_id' => $config['access_key_id'] ?? '',
                'access_key_secret' => $config['access_key_secret'] ?? '',
                'bucket' => $config['bucket'] ?? '',
                'endpoint' => $config['endpoint'] ?? '',
                'use_cdn' => $config['use_cdn'] ?? false,
                'cdn_domain' => $config['cdn_domain'] ?? '',
            ],
            'cos' => [
                'driver' => 'cos',
                'secret_id' => $config['secret_id'] ?? '',
                'secret_key' => $config['secret_key'] ?? '',
                'bucket' => $config['bucket'] ?? '',
                'region' => $config['region'] ?? '',
                'use_cdn' => $config['use_cdn'] ?? false,
                'cdn_domain' => $config['cdn_domain'] ?? '',
            ],
            's3' => [
                'driver' => 's3',
                'key' => $config['access_key_id'] ?? '',
                'secret' => $config['access_key_secret'] ?? '',
                'bucket' => $config['bucket'] ?? '',
                'region' => $config['region'] ?? '',
                'endpoint' => $config['endpoint'] ?? '',
                'use_path_style_endpoint' => $config['use_path_style'] ?? false,
            ],
            default => throw new \InvalidArgumentException("Unsupported driver: {$disk->driver}"),
        };

        Storage::extend($disk->name, function ($app, $configOverride) use ($diskConfig) {
            $finalConfig = array_merge($diskConfig, $configOverride ?? []);

            return match ($diskConfig['driver']) {
                'local' => new \Illuminate\Filesystem\FilesystemAdapter(
                    new \Illuminate\Filesystem\LocalFilesystem,
                    new \League\Flysystem\Local\LocalFilesystemAdapter($finalConfig['root']),
                ),
                default => \Illuminate\Support\Facades\Storage::build($finalConfig),
            };
        });
    }
}
