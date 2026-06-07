<?php

namespace App\Livewire\Admin\UploadPolicies;

use App\Models\StorageStrategy;
use App\Models\UploadPolicy;
use App\Services\UploadPolicyService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

#[Layout('livewire.admin.layout')]
class Index extends Component
{
    use WithFileUploads;

    public string $tab = 'files';
    public string $fileFilter = 'all';

    public bool $showUploadModal = false;
    public $uploadFile = null;
    public string $uploadPolicyKey = '';

    protected array $systemKeys = ['theme_zip'];

    protected function rules(): array
    {
        return [
            'uploadFile' => 'required|file|max:51200',
            'uploadPolicyKey' => 'required|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'uploadFile.required' => '请选择要上传的文件。',
            'uploadFile.max' => '文件大小不能超过 50MB。',
            'uploadPolicyKey.required' => '请选择上传策略。',
        ];
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function setFileFilter(string $filter): void
    {
        $this->fileFilter = $filter;
    }

    public function openUploadModal(): void
    {
        $this->showUploadModal = true;
        $this->uploadFile = null;
        $this->uploadPolicyKey = '';
        $this->resetValidation();
    }

    public function closeUploadModal(): void
    {
        $this->showUploadModal = false;
        $this->uploadFile = null;
        $this->uploadPolicyKey = '';
        $this->resetValidation();
    }

    public function doUpload(): void
    {
        $this->validate();

        try {
            $service = app(UploadPolicyService::class);
            $service->store($this->uploadFile, $this->uploadPolicyKey);

            session()->flash('success', '文件上传成功。');
            $this->closeUploadModal();
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', '上传失败：' . $e->getMessage());
        }
    }

    public function togglePolicy(string $key): void
    {
        if (in_array($key, $this->systemKeys)) {
            session()->flash('error', '系统策略不可修改。');
            return;
        }

        $policy = UploadPolicy::where('key', $key)->firstOrFail();
        $policy->update(['is_active' => ! $policy->is_active]);

        session()->flash('success', $policy->is_active ? "「{$policy->label}」已启用。" : "「{$policy->label}」已禁用。");
    }

    public function deletePolicy(string $key): void
    {
        if (in_array($key, $this->systemKeys)) {
            session()->flash('error', '系统策略不可删除。');
            return;
        }

        $policy = UploadPolicy::where('key', $key)->firstOrFail();
        $policy->delete();

        session()->flash('success', "「{$policy->label}」已删除。");
    }

    public function toggleStrategy(string $key): void
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

    public function deleteStrategy(string $key): void
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

    public function deleteFile(string $path): void
    {
        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            $disk->delete($path);
            session()->flash('success', '文件已删除。');
        } else {
            session()->flash('error', '文件不存在。');
        }
    }

    public function getFiles(): array
    {
        $disk = Storage::disk('public');

        if (! $disk->exists('')) {
            return [];
        }

        $allFiles = $disk->allFiles();
        $allFiles = array_values(array_filter($allFiles, fn ($f) => basename($f) !== '.gitignore'));
        $policies = UploadPolicy::all()->keyBy('key');

        $files = [];
        foreach ($allFiles as $path) {
            $size = $disk->size($path);
            $lastModified = $disk->lastModified($path);

            $policyKey = 'uncategorized';
            foreach ($policies as $policy) {
                $prefix = ltrim($policy->path_prefix, '/');
                if ($prefix !== '' && str_starts_with($path, $prefix . '/')) {
                    $policyKey = $policy->key;
                    break;
                }
            }

            $files[] = [
                'path' => $path,
                'name' => basename($path),
                'size' => $size,
                'size_readable' => $this->formatSize($size),
                'last_modified' => $lastModified,
                'last_modified_readable' => date('Y-m-d H:i', $lastModified),
                'policy_key' => $policyKey,
                'policy_label' => $policies[$policyKey]->label ?? '未分类',
                'url' => $disk->url($path),
            ];
        }

        usort($files, fn ($a, $b) => $b['last_modified'] - $a['last_modified']);

        if ($this->fileFilter !== 'all') {
            $files = array_values(array_filter($files, fn ($f) => $f['policy_key'] === $this->fileFilter));
        }

        return $files;
    }

    protected function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    public function render()
    {
        $policies = UploadPolicy::whereNotIn('key', $this->systemKeys)
            ->orderBy('key')
            ->get();

        $strategies = StorageStrategy::orderBy('key')->get();

        $policyCounts = [
            'all' => UploadPolicy::count(),
            'active' => UploadPolicy::where('is_active', true)->count(),
            'inactive' => UploadPolicy::where('is_active', false)->count(),
        ];

        $strategyCounts = [
            'all' => StorageStrategy::count(),
            'active' => StorageStrategy::where('is_active', true)->count(),
            'inactive' => StorageStrategy::where('is_active', false)->count(),
        ];

        $files = $this->tab === 'files' ? $this->getFiles() : [];
        $allPolicies = UploadPolicy::where('is_active', true)->get();

        $fileCount = count(array_filter(Storage::disk('public')->allFiles(), fn ($f) => basename($f) !== '.gitignore'));

        return view('livewire.admin.upload-policies.index', compact(
            'policies', 'strategies', 'policyCounts', 'strategyCounts',
            'files', 'allPolicies', 'fileCount'
        ));
    }
}
