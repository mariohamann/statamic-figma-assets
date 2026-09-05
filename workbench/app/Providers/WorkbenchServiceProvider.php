<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Facades\User;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        config([
            'app.debug' => false,
            'cache.default' => 'file',
            'queue.default' => 'sync',
            'session.driver' => 'file',
            'statamic.users.repository' => 'file',
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (! User::findByEmail('browser@example.test')) {
            User::make()
                ->email('browser@example.test')
                ->password('browser-password')
                ->makeSuper()
                ->save();
        }
    }
}
