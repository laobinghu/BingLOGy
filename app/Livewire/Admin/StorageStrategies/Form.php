<?php

namespace App\Livewire\Admin\StorageStrategies;

use App\Models\StorageStrategy;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.admin.layout')]
class Form extends Component
{
    public ?StorageStrategy $strategy = null;
    public bool $isEditing = false;

    public string $key = '';
    public string $label = '';
    public string $driver = 'local';
    public array $config = [];
    public bool $is_active = true;

    protected array $systemKeys = ['local'];

    public function mount(?StorageStrategy $strategy = null): void
    {
        if ($strategy && in_array($strategy->key, $this->systemKeys)) {
            abort(403, '系统存储策略不可编辑。');
        }

        $this->strategy = $strategy;

        if ($strategy) {
            $this->isEditing = true;
            $this->key = $strategy->key;
            $this->label = $strategy->label;
            $this->driver = $strategy->driver;
            $this->config = $strategy->config ?? [];
            $this->is_active = $strategy->is_active;
        }
    }

    public function rules(): array
    {
        $rules = [
            'key' => 'required|string|max:255|unique:storage_strategies,'.($this->isEditing ? 'id,'.$this->strategy->id : ''),
            'label' => 'required|string|max:255',
            'driver' => 'required|in:local,s3,oss,cos',
            'is_active' => 'boolean',
        ];

        return match ($this->driver) {
            'local' => $rules,
            's3' => array_merge($rules, [
                'config.region' => 'required|string',
                'config.bucket' => 'required|string',
                'config.key' => 'required|string',
                'config.secret' => 'required|string',
                'config.endpoint' => 'nullable|string',
                'config.url' => 'nullable|url',
            ]),
            'oss' => array_merge($rules, [
                'config.region' => 'required|string',
                'config.bucket' => 'required|string',
                'config.key' => 'required|string',
                'config.secret' => 'required|string',
                'config.endpoint' => 'required|string',
                'config.url' => 'nullable|url',
            ]),
            'cos' => array_merge($rules, [
                'config.region' => 'required|string',
                'config.bucket' => 'required|string',
                'config.key' => 'required|string',
                'config.secret' => 'required|string',
                'config.endpoint' => 'required|string',
                'config.url' => 'nullable|url',
            ]),
            default => $rules,
        };
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'key' => $this->key,
            'label' => $this->label,
            'driver' => $this->driver,
            'config' => in_array($this->driver, ['s3', 'oss', 'cos']) ? $this->config : [],
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $this->strategy->update($data);
            session()->flash('success', '存储策略已更新。');
        } else {
            $data['is_default'] = !StorageStrategy::exists();
            StorageStrategy::create($data);
            session()->flash('success', '存储策略已创建。');
        }

        $this->redirect(route('admin.storage-strategies.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.storage-strategies.form');
    }
}
