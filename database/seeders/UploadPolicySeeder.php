<?php

namespace Database\Seeders;

use App\Models\StorageStrategy;
use App\Models\UploadPolicy;
use Illuminate\Database\Seeder;

class UploadPolicySeeder extends Seeder
{
    public function run(): void
    {
        $localStrategy = StorageStrategy::firstOrCreate(
            ['key' => 'local'],
            [
                'label' => '本地存储',
                'driver' => 'local',
                'config' => [],
                'is_default' => true,
                'is_active' => true,
            ]
        );

        $policies = [
            [
                'key' => 'cover_image',
                'label' => '文章封面图',
                'storage_strategy_id' => $localStrategy->id,
                'path_prefix' => 'covers',
                'allowed_mimes' => ['jpg', 'jpeg', 'png', 'webp'],
                'max_size_kb' => 2048,
                'is_active' => true,
            ],
            [
                'key' => 'attachment',
                'label' => '文章附件',
                'storage_strategy_id' => $localStrategy->id,
                'path_prefix' => 'attachments',
                'allowed_mimes' => ['pdf', 'zip', 'jpg', 'jpeg', 'png', 'webp'],
                'max_size_kb' => 10240,
                'is_active' => true,
            ],
            [
                'key' => 'theme_zip',
                'label' => '主题安装包',
                'storage_strategy_id' => $localStrategy->id,
                'path_prefix' => 'tmp/themes',
                'allowed_mimes' => ['zip'],
                'max_size_kb' => 51200,
                'is_active' => true,
            ],
        ];

        foreach ($policies as $policy) {
            UploadPolicy::updateOrCreate(['key' => $policy['key']], $policy);
        }
    }
}
