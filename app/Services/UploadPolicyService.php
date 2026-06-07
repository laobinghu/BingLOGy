<?php

namespace App\Services;

use App\Models\StorageStrategy;
use App\Models\UploadPolicy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UploadPolicyService
{
    public function store(UploadedFile $file, string $key): string
    {
        $policy = UploadPolicy::where('key', $key)
            ->where('is_active', true)
            ->with('storageStrategy')
            ->firstOrFail();

        $this->validate($file, $policy);
        $diskName = $this->resolveDisk($policy);
        $this->ensureDiskRegistered($diskName);

        $relativePath = ltrim($policy->path_prefix, '/');
        if ($relativePath !== '') {
            $relativePath .= '/';
        }
        $relativePath .= $file->hashName();

        Storage::disk($diskName)->putFileAs(
            dirname($relativePath),
            $file,
            basename($relativePath)
        );

        return $relativePath;
    }

    public function delete(?string $path, ?string $key = null): bool
    {
        if (empty($path)) {
            return false;
        }

        $disk = Storage::disk('public');

        if ($key) {
            $policy = UploadPolicy::where('key', $key)->with('storageStrategy')->first();
            if ($policy) {
                $diskName = $this->resolveDisk($policy);
                $this->ensureDiskRegistered($diskName);
                $disk = Storage::disk($diskName);
            }
        }

        return $disk->delete($path);
    }

    public function url(string $path, ?string $key = null): string
    {
        $diskName = 'public';
        if ($key) {
            $policy = UploadPolicy::where('key', $key)->with('storageStrategy')->first();
            if ($policy) {
                $diskName = $this->resolveDisk($policy);
            }
        }

        $url = config("filesystems.disks.{$diskName}.url", rtrim(config('app.url'), '/'));

        return rtrim($url, '/').'/'.ltrim($path, '/');
    }

    protected function resolveDisk(UploadPolicy $policy): string
    {
        if ($policy->storageStrategy && $policy->storageStrategy->is_active) {
            $strategy = $policy->storageStrategy;

            if ($strategy->driver === 'local') {
                return 'public';
            }

            return "{$strategy->key}_upload";
        }

        return 'public';
    }

    protected function ensureDiskRegistered(string $diskName): void
    {
        if (in_array($diskName, ['public', 'local'])) {
            return;
        }

        if (Config::has("filesystems.disks.{$diskName}")) {
            return;
        }

        $strategyKey = str_replace('_upload', '', $diskName);
        $strategy = StorageStrategy::where('key', $strategyKey)
            ->where('is_active', true)
            ->first();

        if (!$strategy) {
            return;
        }

        $diskConfig = $this->buildDiskConfig($strategy);

        if ($diskConfig) {
            Config::set("filesystems.disks.{$diskName}", $diskConfig);
        }
    }

    protected function buildDiskConfig(StorageStrategy $strategy): ?array
    {
        $config = $strategy->config ?? [];

        return match ($strategy->driver) {
            's3' => [
                'driver' => 's3',
                'key' => $config['key'] ?? env('AWS_ACCESS_KEY_ID'),
                'secret' => $config['secret'] ?? env('AWS_SECRET_ACCESS_KEY'),
                'region' => $config['region'] ?? env('AWS_DEFAULT_REGION'),
                'bucket' => $config['bucket'] ?? env('AWS_BUCKET'),
                'url' => $config['url'] ?? env('AWS_URL'),
                'endpoint' => $config['endpoint'] ?? env('AWS_ENDPOINT'),
                'use_path_style_endpoint' => $config['use_path_style_endpoint'] ?? env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            ],
            'oss' => [
                'driver' => 's3',
                'key' => $config['key'] ?? env('OSS_ACCESS_KEY_ID'),
                'secret' => $config['secret'] ?? env('OSS_ACCESS_KEY_SECRET'),
                'region' => $config['region'] ?? env('OSS_REGION'),
                'bucket' => $config['bucket'] ?? env('OSS_BUCKET'),
                'endpoint' => $config['endpoint'] ?? env('OSS_ENDPOINT'),
                'use_path_style_endpoint' => true,
            ],
            'cos' => [
                'driver' => 's3',
                'key' => $config['key'] ?? env('COS_SECRET_ID'),
                'secret' => $config['secret'] ?? env('COS_SECRET_KEY'),
                'region' => $config['region'] ?? env('COS_REGION'),
                'bucket' => $config['bucket'] ?? env('COS_BUCKET'),
                'endpoint' => $config['endpoint'] ?? env('COS_ENDPOINT'),
                'use_path_style_endpoint' => true,
            ],
            default => null,
        };
    }

    public function validationRules(string $key): array
    {
        $policy = UploadPolicy::where('key', $key)
            ->where('is_active', true)
            ->first();

        if (! $policy) {
            return [];
        }

        return [
            'file' => [
                'nullable',
                'file',
                'mimetypes:'.implode(',', $this->normalizeMimes($policy->allowed_mimes)),
                'max:'.$policy->max_size_kb,
            ],
        ];
    }

    protected function validate(UploadedFile $file, UploadPolicy $policy): void
    {
        $maxBytes = $policy->max_size_kb * 1024;

        if ($file->getSize() > $maxBytes) {
            throw ValidationException::withMessages([
                'file' => "文件大小不能超过 {$policy->max_size_kb} KB。",
            ]);
        }

        $allowed = $this->normalizeMimes($policy->allowed_mimes);
        $mime = $file->getMimeType();

        if (! in_array($mime, $allowed)) {
            throw ValidationException::withMessages([
                'file' => "不支持的文件类型：{$mime}。允许的类型：".implode(', ', $allowed)."。",
            ]);
        }
    }

    protected function normalizeMimes(array $mimes): array
    {
        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'zip' => 'application/zip',
            'pdf' => 'application/pdf',
        ];

        $result = [];
        foreach ($mimes as $mime) {
            $result[] = $map[strtolower($mime)] ?? $mime;
        }

        return array_unique($result);
    }
}
