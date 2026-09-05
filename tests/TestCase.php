<?php

namespace MarioHamann\StatamicFigmaAssets\Tests;

use Illuminate\Support\Facades\Storage;
use MarioHamann\StatamicFigmaAssets\ServiceProvider;
use Statamic\Facades\AssetContainer;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

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
