<?php

namespace App\Livewire\Admin\UploadPolicies;

use App\Models\StorageStrategy;
use App\Models\UploadPolicy;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('admin.layout')]
class Form extends Component
{
    public ?UploadPolicy $policy = null;
    public bool $isEditing = false;

    public string $key = '';
    public string $label = '';
    public ?int $storage_strategy_id = null;
    public string $path_prefix = '';
    public array $allowed_mimes = [];
    public int $max_size_kb = 2048;
    public bool $is_active = true;

    protected array $systemKeys = ['theme_zip'];

    public function mount(?UploadPolicy $policy = null): void
    {
        if ($policy && in_array($policy->key, $this->systemKeys)) {
            abort(403, '系统策略不可编辑。');
        }

        $this->policy = $policy;

        if ($policy) {
            $this->isEditing = true;
            $this->key = $policy->key;
            $this->label = $policy->label;
            $this->storage_strategy_id = $policy->storage_strategy_id;
            $this->path_prefix = $policy->path_prefix;
            $this->allowed_mimes = $policy->allowed_mimes;
            $this->max_size_kb = $policy->max_size_kb;
            $this->is_active = $policy->is_active;
        }
    }

    public function toggleMime(string $mime): void
    {
        if (in_array($mime, $this->allowed_mimes)) {
            $this->allowed_mimes = array_values(array_filter($this->allowed_mimes, fn ($m) => $m !== $mime));
        } else {
            $this->allowed_mimes[] = $mime;
        }
    }

    public function rules(): array
    {
        return [
            'key' => 'required|string|max:255|unique:upload_policies,'.($this->isEditing ? 'id,'.$this->policy->id : ''),
            'label' => 'required|string|max:255',
            'storage_strategy_id' => 'nullable|exists:storage_strategies,id',
            'path_prefix' => 'nullable|string|max:255',
            'allowed_mimes' => 'required|array|min:1',
            'allowed_mimes.*' => 'string|max:50',
            'max_size_kb' => 'required|integer|min:1|max:51200',
            'is_active' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'key' => $this->key,
            'label' => $this->label,
            'storage_strategy_id' => $this->storage_strategy_id,
            'path_prefix' => ltrim($this->path_prefix, '/'),
            'allowed_mimes' => $this->allowed_mimes,
            'max_size_kb' => $this->max_size_kb,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $this->policy->update($data);
            session()->flash('success', '上传策略已更新。');
        } else {
            UploadPolicy::create($data);
            session()->flash('success', '上传策略已创建。');
        }

        $this->redirect(route('admin.upload-policies.index'), navigate: true);
    }

    public function render()
    {
        return view('admin.upload-policies.form');
    }
}
