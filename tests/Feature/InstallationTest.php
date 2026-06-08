<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\InstallationManager;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class InstallationTest extends TestCase
{
    use DatabaseMigrations;

    protected function tearDown(): void
    {
        InstallationManager::unmarkInstalled();
        File::deleteDirectory(storage_path('framework/testing'));

        parent::tearDown();
    }

    public function test_guests_are_redirected_to_install_when_app_is_not_installed(): void
    {
        InstallationManager::unmarkInstalled();

        $response = $this->get(route('home'));

        $response->assertRedirect(route('install.index'));
    }

    public function test_install_page_is_available_before_installation(): void
    {
        InstallationManager::unmarkInstalled();

        $response = $this->get(route('install.index'));

        $response->assertOk();
        $response->assertSee('多页安装向导');
    }

    public function test_install_wizard_advances_through_each_step_and_completes_installation(): void
    {
        InstallationManager::unmarkInstalled();
        $installToken = InstallationManager::ensureInstallToken();
        $sqlitePath = storage_path('framework/testing/install-'.Str::uuid().'.sqlite');

        $this->post(route('install.database.store'), [
            'install_token' => $installToken,
            'db_connection' => 'sqlite',
            'db_database' => $sqlitePath,
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_username' => '',
            'db_password' => '',
        ])->assertRedirect(route('install.site'));

        $this->post(route('install.site.store'), [
            'install_token' => $installToken,
            'app_name' => 'BingLOGy',
            'app_url' => 'http://localhost',
            'app_timezone' => 'Asia/Shanghai',
        ])->assertRedirect(route('install.admin'));

        $this->post(route('install.admin.store'), [
            'install_token' => $installToken,
            'admin_name' => 'Admin',
            'admin_email' => 'admin@example.com',
            'admin_password' => 'password123',
        ])->assertRedirect(route('install.review'));

        $response = $this->post(route('install.finish'), [
            'install_token' => $installToken,
        ]);

        $response->assertRedirect(route('install.complete'));

        $this->assertTrue(InstallationManager::isInstalled());
        $this->assertTrue(User::where('email', 'admin@example.com')->exists());
        $this->assertTrue(Role::where('name', 'admin')->exists());
        $this->assertFileExists($sqlitePath);
    }

    public function test_install_page_redirects_after_installation(): void
    {
        InstallationManager::markInstalled(['app_name' => 'BingLOGy']);

        $response = $this->get(route('install.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_installation_manager_status_uses_nested_check_groups(): void
    {
        $status = InstallationManager::status();

        $this->assertArrayHasKey('runtime', $status['checks']);
        $this->assertArrayHasKey('extensions', $status['checks']);
        $this->assertArrayHasKey('filesystem', $status['checks']);
        $this->assertArrayHasKey('database', $status['checks']);
        $this->assertArrayHasKey('connection', $status['checks']['database']);
    }

    public function test_install_completion_page_is_available_after_installation(): void
    {
        InstallationManager::markInstalled(['app_name' => 'BingLOGy', 'admin_email' => 'admin@example.com']);

        $response = $this->get(route('install.complete'));

        $response->assertOk();
        $response->assertSee('安装完成');
    }
}
