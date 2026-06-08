<?php

namespace App\Providers;

use App\Models\Tag;
use App\Observers\TagObserver;
use App\Services\InstallationManager;
use App\Services\StorageManager;
use App\Services\PluginManager;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Pulse\Pulse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! InstallationManager::isInstalled()) {
            return;
        }

        StorageManager::registerDisks();
        PluginManager::loadActivePlugins();

        Tag::observe(TagObserver::class);

        Gate::before(fn ($user, $ability) => $user->id === 1 ? true : null);

        Gate::define('viewPulse', fn (mixed $user) => $user !== null);

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
