<?php

namespace MarioHamann\StatamicFigmaAssets\Tests;

use Illuminate\Support\Facades\Storage;
use MarioHamann\StatamicFigmaAssets\ServiceProvider;
use Statamic\Facades\AssetContainer;
use Statamic\Testing\AddonTestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

abstract class TestCase extends AddonTestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected string $addonServiceProvider = ServiceProvider::class;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('cache.default', 'array');
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('filesystems.disks.figma-assets', [
            'driver' => 'local',
            'root' => storage_path('framework/testing/figma-assets'),
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('figma-assets');

        AssetContainer::make()
            ->handle('figma-assets')
            ->disk('figma-assets')
            ->save();
    }
}
