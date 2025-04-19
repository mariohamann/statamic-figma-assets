<?php

namespace MarioHamann\StatamicFigmaAssets;

use Statamic\Providers\AddonServiceProvider;
use Statamic\Facades\Utility;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        Utility::register('figma_assets')
            ->action([Controller::class, 'index'])
            ->title('Figma Assets')
            ->icon('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M0 5.56A3.53 3.53 0 0 1 3.5 2h6C11.43 2 13 3.6 13 5.56c0 1.24-.63 2.33-1.58 2.97A3.56 3.56 0 0 1 13 11.5a3.53 3.53 0 0 1-3.5 3.56h-.07c-.91 0-1.74-.35-2.36-.93v3.28c0 1.99-1.6 3.59-3.55 3.59a3.57 3.57 0 0 1-1.94-6.53A3.57 3.57 0 0 1 0 11.5c0-1.24.63-2.34 1.58-2.97A3.57 3.57 0 0 1 0 5.56ZM5.93 9.1H3.5a2.37 2.37 0 0 0-2.35 2.39 2.37 2.37 0 0 0 2.33 2.39h2.45V9.11Zm1.14 2.39a2.37 2.37 0 0 0 2.36 2.39h.07c1.3 0 2.35-1.07 2.35-2.39A2.37 2.37 0 0 0 9.5 9.11h-.07a2.37 2.37 0 0 0-2.36 2.39ZM3.5 15.06h-.02a2.37 2.37 0 0 0-2.33 2.38 2.38 2.38 0 1 0 4.78-.03v-2.35H3.5Zm0-11.9a2.37 2.37 0 0 0-2.35 2.4A2.37 2.37 0 0 0 3.5 7.94h2.43V3.17H3.5Zm3.57 4.78H9.5c1.3 0 2.35-1.06 2.35-2.38a2.37 2.37 0 0 0-2.35-2.4H7.07v4.78Z"/><path fill="currentColor" d="M20.5 9a.5.5 0 0 0-1 0h1Zm-.85 14.35c.2.2.5.2.7 0l3.19-3.18a.5.5 0 1 0-.71-.7L20 22.28l-2.83-2.83a.5.5 0 1 0-.7.71l3.18 3.18ZM19.5 9v14h1V9h-1Z"/></svg>')
            ->description('Import your assets from Figma.')
            ->routes(function ($router) {
                $router->post('/info/{configIndex}', [Controller::class, 'info'])->name('info');
                $router->post('/import/{configIndex}', [Controller::class, 'import'])->name('import');
                $router->post('/reimport/{configIndex}', [Controller::class, 'reimport'])->name('reimport');
                $router->get('/progress/{configIndex}', [Controller::class, 'progress'])->name('progress');
            });

        $this->publishes([
            __DIR__ . '/../config/statamic-figma-assets.php' => config_path('statamic-figma-assets.php'),
        ], 'config');
    }
}
