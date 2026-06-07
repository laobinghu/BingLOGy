<?php

namespace App\Livewire\Admin;

use App\Services\SettingsManager;
use App\Services\UploadPolicyService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('livewire.admin.layout')]
class BlogSettings extends Component
{
    use WithFileUploads;

    public string $tab = 'basic';

    public string $site_name = '';
    public string $site_description = '';
    public string $site_logo_url = '';
    public string $favicon_url = '';

    public string $seo_keywords = '';
    public string $custom_head = '';

    public int $posts_per_page = 20;
    public string $theme_default = 'auto';
    public string $theme_current = '';
    public string $custom_css = '';

    public bool $allow_registration = false;
    public bool $public_access = true;
    public bool $rss_enabled = true;
    public bool $maintenance_mode = false;

    public $themeZip = null;

    public function mount(): void
    {
        $this->site_name = SettingsManager::get('site_name', config('app.name', 'BingLOGy'));
        $this->site_description = SettingsManager::get('site_description', '');
        $this->site_logo_url = SettingsManager::get('site_logo_url', '');
        $this->favicon_url = SettingsManager::get('favicon_url', '');

        $this->seo_keywords = SettingsManager::get('seo_keywords', '');
        $this->custom_head = SettingsManager::get('custom_head', '');

        $this->posts_per_page = (int) SettingsManager::get('posts_per_page', 20);
        $this->theme_default = SettingsManager::get('theme_default', 'auto');
        $this->theme_current = SettingsManager::get('theme_current', '');
        $this->custom_css = SettingsManager::get('custom_css', '');

        $this->allow_registration = (bool) SettingsManager::get('allow_registration', false);
        $this->public_access = (bool) SettingsManager::get('public_access', true);
        $this->rss_enabled = (bool) SettingsManager::get('rss_enabled', true);
        $this->maintenance_mode = (bool) SettingsManager::get('maintenance_mode', false);
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function save(): void
    {
        $this->validate([
            'site_name' => 'required|max:255',
            'posts_per_page' => 'required|integer|min:1|max:100',
            'theme_default' => 'in:auto,dark,light',
        ]);

        SettingsManager::set('site_name', $this->site_name);
        SettingsManager::set('site_description', $this->site_description);
        SettingsManager::set('site_logo_url', $this->site_logo_url);
        SettingsManager::set('favicon_url', $this->favicon_url);

        SettingsManager::set('seo_keywords', $this->seo_keywords);
        SettingsManager::set('custom_head', $this->custom_head);

        SettingsManager::set('posts_per_page', $this->posts_per_page);
        SettingsManager::set('theme_default', $this->theme_default);
        SettingsManager::set('theme_current', $this->theme_current);
        SettingsManager::set('custom_css', $this->custom_css);

        SettingsManager::set('allow_registration', $this->allow_registration);
        SettingsManager::set('public_access', $this->public_access);
        SettingsManager::set('rss_enabled', $this->rss_enabled);
        SettingsManager::set('maintenance_mode', $this->maintenance_mode);

        session()->flash('success', '博客设置已保存。');
    }

    public function uploadThemeZip(): void
    {
        $this->validate([
            'themeZip' => 'required|file|mimes:zip',
        ]);

        $uploadService = app(UploadPolicyService::class);
        $tmpPath = $uploadService->store($this->themeZip, 'theme_zip');

        $zip = new \ZipArchive;
        $res = $zip->open(storage_path("app/{$tmpPath}"));

        if ($res !== true) {
            $uploadService->delete($tmpPath, 'theme_zip');
            session()->flash('error', '无法打开 ZIP 文件。');
            return;
        }

        $dirName = uniqid('theme_');
        $extractPath = resource_path("views/themes/{$dirName}");

        if (! is_dir(dirname($extractPath))) {
            mkdir(dirname($extractPath), 0755, true);
        }

        $zip->extractTo($extractPath);
        $zip->close();

        $uploadService->delete($tmpPath, 'theme_zip');

        $this->themeZip = null;

        session()->flash('success', "主题已上传至「{$dirName}」。");
    }

    public function getThemesProperty(): array
    {
        $themesPath = resource_path('views/themes');

        if (! is_dir($themesPath)) {
            return [];
        }

        $dirs = array_filter(scandir($themesPath), fn ($item) => $item !== '.' && $item !== '..' && is_dir("{$themesPath}/{$item}"));

        return array_values($dirs);
    }

    public function render()
    {
        return view('livewire.admin.blog-settings');
    }
}
