<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\InstallationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class InstallController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (InstallationManager::isInstalled()) {
            return redirect()->route('home');
        }

        return view('pages.install.wizard', $this->wizardData('intro'));
    }

    public function database(): View|RedirectResponse
    {
        return $this->wizardPage('database');
    }

    public function storeDatabase(Request $request): RedirectResponse|View
    {
        return $this->storeStep($request, 'database', [
            'install_token' => ['required', 'string'],
            'db_connection' => ['required', 'in:sqlite,mysql,mariadb,pgsql,sqlsrv'],
            'db_database' => ['required', 'string', 'max:255'],
            'db_host' => ['nullable', 'string', 'max:255'],
            'db_port' => ['nullable', 'string', 'max:20'],
            'db_username' => ['nullable', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ]);
    }

    public function site(): View|RedirectResponse
    {
        $draft = InstallationManager::loadInstallDraft();

        if ($draft['step'] === 'intro') {
            return redirect()->route('install.database');
        }

        return view('pages.install.wizard', $this->wizardData('site'));
    }

    public function storeSite(Request $request): RedirectResponse|View
    {
        return $this->storeStep($request, 'site', [
            'install_token' => ['required', 'string'],
            'app_name' => ['required', 'string', 'max:255'],
            'app_url' => ['required', 'url', 'max:255'],
            'app_timezone' => ['required', 'string', 'max:64'],
        ]);
    }

    public function admin(): View|RedirectResponse
    {
        $draft = InstallationManager::loadInstallDraft();

        if ($draft['step'] === 'intro') {
            return redirect()->route('install.database');
        }

        return view('pages.install.wizard', $this->wizardData('admin'));
    }

    public function storeAdmin(Request $request): RedirectResponse|View
    {
        return $this->storeStep($request, 'admin', [
            'install_token' => ['required', 'string'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8', 'max:255'],
        ]);
    }

    public function review(): View|RedirectResponse
    {
        $draft = InstallationManager::loadInstallDraft();

        if (($draft['data']['admin_name'] ?? '') === '') {
            return redirect()->route('install.admin');
        }

        return view('pages.install.wizard', $this->wizardData('review'));
    }

    public function finish(Request $request): RedirectResponse|View
    {
        if (InstallationManager::isInstalled()) {
            return redirect()->route('install.complete');
        }

        $validator = Validator::make($request->all(), [
            'install_token' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return view('pages.install.wizard', $this->wizardData('review', ['安装令牌无效，请返回重试。']));
        }

        $validated = $validator->validated();

        if (! InstallationManager::verifyInstallToken($validated['install_token'])) {
            return view('pages.install.wizard', $this->wizardData('review', ['安装令牌无效，请返回重试。']));
        }

        $draft = InstallationManager::loadInstallDraft();
        $data = $draft['data'] ?? [];
        $snapshot = $this->snapshotEnvironment();
        $preparedDatabasePath = null;

        $required = ['db_connection', 'db_database', 'app_name', 'app_url', 'app_timezone', 'admin_name', 'admin_email', 'admin_password'];
        foreach ($required as $key) {
            if (! filled($data[$key] ?? null)) {
                return redirect()->route('install.index');
            }
        }

        try {
            $preparedDatabasePath = $this->prepareDatabase($data['db_connection'], $data['db_database']);

            if (! filled(config('app.key'))) {
                $this->setEnvValue('APP_KEY', 'base64:'.base64_encode(random_bytes(32)));
            }

            $this->setEnvValue('APP_NAME', $data['app_name']);
            $this->setEnvValue('APP_URL', $data['app_url']);
            $this->setEnvValue('APP_TIMEZONE', $data['app_timezone']);
            $this->setEnvValue('DB_CONNECTION', $data['db_connection']);
            $this->setEnvValue('DB_DATABASE', $data['db_database']);
            $this->setEnvValue('DB_HOST', $data['db_host'] ?? '127.0.0.1');
            $this->setEnvValue('DB_PORT', $data['db_port'] ?? match ($data['db_connection']) {
                'pgsql' => '5432',
                'sqlsrv' => '1433',
                default => '3306',
            });
            $this->setEnvValue('DB_USERNAME', $data['db_username'] ?? '');
            $this->setEnvValue('DB_PASSWORD', $data['db_password'] ?? '');
            $this->setEnvValue('MAIL_MAILER', $data['mail_mailer'] ?? 'log');
            $this->setEnvValue('MAIL_HOST', $data['mail_host'] ?? '');
            $this->setEnvValue('MAIL_PORT', (string) ($data['mail_port'] ?? 2525));
            $this->setEnvValue('MAIL_USERNAME', $data['mail_username'] ?? '');
            $this->setEnvValue('MAIL_PASSWORD', $data['mail_password'] ?? '');
            $this->setEnvValue('MAIL_FROM_ADDRESS', $data['mail_from_address'] ?? 'hello@example.com');
            $this->setEnvValue('MAIL_FROM_NAME', $data['mail_from_name'] ?? $data['app_name']);
            $this->setEnvValue('CACHE_STORE', $data['cache_store'] ?? 'database');
            $this->setEnvValue('QUEUE_CONNECTION', $data['queue_connection'] ?? 'database');
            $this->setEnvValue('SESSION_DRIVER', $data['session_driver'] ?? 'database');
            $this->setEnvValue('FILESYSTEM_DISK', $data['filesystem_disk'] ?? 'local');

            config([
                'app.name' => $data['app_name'],
                'app.url' => $data['app_url'],
                'app.timezone' => $data['app_timezone'],
                'database.default' => $data['db_connection'],
            ]);

            DB::purge($data['db_connection']);
            DB::setDefaultConnection($data['db_connection']);

            Artisan::call('migrate', ['--force' => true]);

            DB::transaction(function () use ($data): void {
                Role::firstOrCreate(['name' => 'admin'], ['label' => 'Administrator']);
                Role::firstOrCreate(['name' => 'editor'], ['label' => 'Editor']);
                Role::firstOrCreate(['name' => 'user'], ['label' => 'User']);

                $user = User::updateOrCreate(
                    ['email' => Str::lower($data['admin_email'])],
                    [
                        'name' => $data['admin_name'],
                        'password' => Hash::make($data['admin_password']),
                        'email_verified_at' => now(),
                    ]
                );

                $user->assignRole('admin');

                Setting::updateOrCreate(
                    ['key' => 'site_name'],
                    ['value' => json_encode($data['app_name'], JSON_UNESCAPED_UNICODE)]
                );
            });

            InstallationManager::markInstalled([
                'app_name' => $data['app_name'],
                'app_url' => $data['app_url'],
                'admin_email' => Str::lower($data['admin_email']),
            ]);
            InstallationManager::clearInstallDraft();

            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('optimize:clear');

            return redirect()->route('install.complete');
        } catch (Throwable $e) {
            $this->restoreEnvironment($snapshot, $preparedDatabasePath);
            report($e);

            return view('pages.install.wizard', $this->wizardData('review', ['安装失败：'.$e->getMessage()]));
        }
    }

    public function complete(): View|RedirectResponse
    {
        if (! InstallationManager::isInstalled()) {
            return redirect()->route('install.index');
        }

        return view('pages.install.complete', [
            'payload' => InstallationManager::installedPayload(),
        ]);
    }

    protected function wizardPage(string $step): View|RedirectResponse
    {
        $draft = InstallationManager::loadInstallDraft();

        if ($step !== 'database' && ($draft['step'] === 'intro')) {
            return redirect()->route('install.database');
        }

        return view('pages.install.wizard', $this->wizardData($step));
    }

    protected function storeStep(Request $request, string $step, array $rules): RedirectResponse|View
    {
        if (InstallationManager::isInstalled()) {
            return redirect()->route('home');
        }

        $validator = Validator::make($request->all(), $rules);
        $form = array_merge(InstallationManager::loadInstallDraft()['data'] ?? [], $request->all());

        if ($validator->fails()) {
            return view('pages.install.wizard', $this->wizardData($step, $validator->errors()->all(), $form));
        }

        $validated = $validator->validated();

        if (! InstallationManager::verifyInstallToken($validated['install_token'])) {
            return view('pages.install.wizard', $this->wizardData($step, ['安装令牌无效，请刷新后重试。'], $form));
        }

        $data = Arr::except($validated, ['install_token']);
        InstallationManager::saveInstallDraft($data, $step);

        return match ($step) {
            'database' => redirect()->route('install.site'),
            'site' => redirect()->route('install.admin'),
            'admin' => redirect()->route('install.review'),
            default => redirect()->route('install.index'),
        };
    }

    protected function wizardData(string $step, array $errorsList = [], array $form = []): array
    {
        $draft = InstallationManager::loadInstallDraft();
        $form = array_merge($this->defaultFormValues(), $draft['data'] ?? [], $form);

        return [
            'status' => InstallationManager::status(),
            'installToken' => InstallationManager::ensureInstallToken(),
            'form' => $form,
            'errorsList' => $errorsList,
            'step' => $step,
            'steps' => [
                'intro' => '环境检查',
                'database' => '数据库',
                'site' => '站点信息',
                'admin' => '管理员',
                'review' => '确认',
            ],
            'draft' => $draft,
        ];
    }

    protected function defaultFormValues(): array
    {
        return [
            'app_name' => config('app.name', 'BingLOGy'),
            'app_url' => config('app.url', 'http://localhost'),
            'app_timezone' => config('app.timezone', 'Asia/Shanghai'),
            'db_connection' => config('database.default', 'sqlite'),
            'db_database' => $this->defaultSQLitePath(),
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_username' => '',
            'db_password' => '',
            'mail_mailer' => config('mail.default', 'log'),
            'mail_host' => '127.0.0.1',
            'mail_port' => 2525,
            'mail_username' => '',
            'mail_password' => '',
            'mail_from_address' => config('mail.from.address', 'hello@example.com'),
            'mail_from_name' => config('mail.from.name', config('app.name', 'BingLOGy')),
            'cache_store' => config('cache.default', 'database'),
            'queue_connection' => config('queue.default', 'database'),
            'session_driver' => config('session.driver', 'database'),
            'filesystem_disk' => config('filesystems.default', 'local'),
            'admin_name' => '',
            'admin_email' => '',
            'admin_password' => '',
        ];
    }

    protected function defaultSQLitePath(): string
    {
        $database = (string) config('database.connections.sqlite.database', 'database/database.sqlite');

        if ($database === '') {
            return 'database/database.sqlite';
        }

        $base = base_path().DIRECTORY_SEPARATOR;

        if (str_starts_with($database, $base)) {
            return ltrim(substr($database, strlen($base)), DIRECTORY_SEPARATOR);
        }

        return $database;
    }

    protected function snapshotEnvironment(): array
    {
        $envPath = base_path('.env');
        $sqlitePath = config('database.connections.sqlite.database');

        return [
            'env_exists' => File::exists($envPath),
            'env_contents' => File::exists($envPath) ? File::get($envPath) : null,
            'installed' => InstallationManager::isInstalled(),
            'sqlite_path' => $sqlitePath,
            'sqlite_exists' => is_string($sqlitePath) && File::exists($sqlitePath),
            'config' => [
                'app.name' => config('app.name'),
                'app.url' => config('app.url'),
                'app.timezone' => config('app.timezone'),
                'app.key' => config('app.key'),
                'database.default' => config('database.default'),
                'database.connections.sqlite.database' => $sqlitePath,
            ],
        ];
    }

    protected function restoreEnvironment(array $snapshot, ?string $preparedDatabasePath = null): void
    {
        if (($snapshot['env_exists'] ?? false) && is_string($snapshot['env_contents'] ?? null)) {
            File::put(base_path('.env'), $snapshot['env_contents']);
        } elseif (File::exists(base_path('.env'))) {
            File::delete(base_path('.env'));
        }

        DB::disconnect('sqlite');
        DB::disconnect($snapshot['config']['database.default'] ?? config('database.default'));

        config($snapshot['config'] ?? []);

        if (! ($snapshot['installed'] ?? false)) {
            InstallationManager::unmarkInstalled();
        }

        $sqlitePath = $preparedDatabasePath ?: ($snapshot['sqlite_path'] ?? null);
        $shouldDeletePreparedDatabase = is_string($preparedDatabasePath)
            && (
                ! ($snapshot['sqlite_exists'] ?? false)
                || $preparedDatabasePath !== ($snapshot['sqlite_path'] ?? null)
            );

        if ($shouldDeletePreparedDatabase && File::exists($preparedDatabasePath)) {
            File::delete($preparedDatabasePath);
        } elseif (is_string($sqlitePath) && ! ($snapshot['sqlite_exists'] ?? false) && File::exists($sqlitePath)) {
            File::delete($sqlitePath);
        }

        DB::purge('sqlite');
        DB::purge($snapshot['config']['database.default'] ?? config('database.default'));
    }

    protected function prepareDatabase(string $connection, string $database): ?string
    {
        if ($connection !== 'sqlite') {
            DB::connection($connection)->getPdo();

            return null;
        }

        $path = $database !== '' ? $database : 'database/database.sqlite';

        if (! str_starts_with($path, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:\\\\/', $path)) {
            $path = base_path($path);
        }

        $directory = dirname($path);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (! File::exists($path)) {
            File::put($path, '');
        }

        if (! is_writable($path)) {
            throw new \RuntimeException("SQLite database file is not writable: {$path}");
        }

        DB::purge('sqlite');
        config(['database.connections.sqlite.database' => $path]);
        DB::connection('sqlite')->getPdo();

        return $path;
    }

    protected function setEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            File::copy(base_path('.env.example'), $envPath);
        }

        $content = File::get($envPath);
        $escaped = str_replace(['\\', '$', "\n", "\r"], ['\\\\', '\$', '\\n', ''], $value);
        $pattern = "/^{$key}=.*/m";
        $replacement = "{$key}={$escaped}";

        if (preg_match($pattern, $content) === 1) {
            $content = preg_replace($pattern, $replacement, $content);
        } else {
            $content .= PHP_EOL.$replacement.PHP_EOL;
        }

        File::put($envPath, $content);
    }
}
