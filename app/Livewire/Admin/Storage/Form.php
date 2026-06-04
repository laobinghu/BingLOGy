<?php

namespace App\Livewire\Admin\Storage;

use App\Models\StorageDisk;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    public ?StorageDisk $disk = null;
    public string $name = '';
    public string $driver = 'local';
    public bool $isEditing = false;

    public string $config_path = '';
    public string $config_access_key_id = '';
    public string $config_access_key_secret = '';
    public string $config_bucket = '';
    public string $config_endpoint = '';
    public string $config_region = '';
    public bool $config_use_cdn = false;
    public string $config_cdn_domain = '';
    public string $config_secret_id = '';
    public string $config_secret_key = '';
    public bool $config_use_path_style = false;

    public function mount(?StorageDisk $disk = null): void
    {
        $this->disk = $disk;

        if ($disk) {
            $this->isEditing = true;
            $this->name = $disk->name;
            $this->driver = $disk->driver;

            $config = $disk->config;
            $this->config_path = $config['path'] ?? '';
            $this->config_access_key_id = $config['access_key_id'] ?? '';
            $this->config_access_key_secret = $config['access_key_secret'] ?? '';
            $this->config_bucket = $config['bucket'] ?? '';
            $this->config_endpoint = $config['endpoint'] ?? '';
            $this->config_region = $config['region'] ?? '';
            $this->config_use_cdn = $config['use_cdn'] ?? false;
            $this->config_cdn_domain = $config['cdn_domain'] ?? '';
            $this->config_secret_id = $config['secret_id'] ?? '';
            $this->config_secret_key = $config['secret_key'] ?? '';
            $this->config_use_path_style = $config['use_path_style'] ?? false;
        }
    }

    public function updatedDriver(): void
    {
        $this->reset([
            'config_path', 'config_access_key_id', 'config_access_key_secret',
            'config_bucket', 'config_endpoint', 'config_region',
            'config_use_cdn', 'config_cdn_domain',
            'config_secret_id', 'config_secret_key', 'config_use_path_style',
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|max:255',
            'driver' => 'required|in:local,oss,cos,s3',
        ];

        return match ($this->driver) {
            'local' => [...$rules, 'config_path' => 'required'],
            'oss' => [...$rules,
                'config_access_key_id' => 'required',
                'config_access_key_secret' => 'required',
                'config_bucket' => 'required',
                'config_endpoint' => 'required',
                'config_cdn_domain' => 'required_if:config_use_cdn,true',
            ],
            'cos' => [...$rules,
                'config_secret_id' => 'required',
                'config_secret_key' => 'required',
                'config_bucket' => 'required',
                'config_region' => 'required',
            ],
            's3' => [...$rules,
                'config_access_key_id' => 'required',
                'config_access_key_secret' => 'required',
                'config_bucket' => 'required',
                'config_region' => 'required',
                'config_endpoint' => 'required',
            ],
            default => $rules,
        };
    }

    protected function buildConfig(): array
    {
        $base = match ($this->driver) {
            'local' => ['path' => $this->config_path],
            'oss' => array_filter([
                'access_key_id' => $this->config_access_key_id,
                'access_key_secret' => $this->config_access_key_secret,
                'bucket' => $this->config_bucket,
                'endpoint' => $this->config_endpoint,
                'use_cdn' => $this->config_use_cdn,
                'cdn_domain' => $this->config_use_cdn ? $this->config_cdn_domain : null,
            ]),
            'cos' => array_filter([
                'secret_id' => $this->config_secret_id,
                'secret_key' => $this->config_secret_key,
                'bucket' => $this->config_bucket,
                'region' => $this->config_region,
                'use_cdn' => $this->config_use_cdn,
                'cdn_domain' => $this->config_use_cdn ? $this->config_cdn_domain : null,
            ]),
            's3' => array_filter([
                'access_key_id' => $this->config_access_key_id,
                'access_key_secret' => $this->config_access_key_secret,
                'bucket' => $this->config_bucket,
                'region' => $this->config_region,
                'endpoint' => $this->config_endpoint,
                'use_path_style' => $this->config_use_path_style,
            ]),
            default => [],
        };

        return $base;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'driver' => $this->driver,
            'config' => $this->buildConfig(),
        ];

        if ($this->isEditing) {
            $this->disk->update($data);
            session()->flash('success', '存储配置已更新。');
        } else {
            $data['is_default'] = !StorageDisk::exists();
            StorageDisk::create($data);
            session()->flash('success', '存储配置已创建。');
        }

        $this->redirect(route('admin.storage.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.storage.form')
            ->layout('layouts.app', ['title' => $this->isEditing ? '编辑存储' : '新增存储']);
    }
}
