<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;

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
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
