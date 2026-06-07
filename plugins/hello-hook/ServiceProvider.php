<?php

namespace Plugins\HelloHook;

use App\Services\HookManager;

class ServiceProvider
{
    public function register(): void
    {
        HookManager::addAction('post.show.head', function ($post) {
            echo '<meta name="hello-hook" content="active">';
        }, 10);
    }
}
