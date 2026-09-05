<?php

namespace MarioHamann\StatamicFigmaAssets\Tests;

use Illuminate\Support\Facades\Http;
use MarioHamann\StatamicFigmaAssets\Controller;
use Mockery;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Facades\AssetContainer;

class ExampleTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->artisan('config:clear');

        parent::tearDown();
    }

    public function test_display_configs_exclude_sensitive_values(): void
    {
        config()->set('statamic-figma-assets', [[
            'title' => 'Icons',
            'token' => 'secret-token',
            'file_id' => 'figma-file',
            'page_title' => 'Exports',
            'assets_container' => null,
        ]]);

        $configs = app(Controller::class)->displayConfigs();

        $this->assertSame('Icons', $configs[0]['title']);
        $this->assertArrayNotHasKey('token', $configs[0]);
        $this->assertArrayNotHasKey('file_id', $configs[0]);
    }

    public function test_figma_requests_use_the_configured_api_base_url(): void
    {
        config()->set('statamic-figma-assets', [[
            'token' => 'test-token',
            'file_id' => 'test-file',
            'page_title' => 'Exports',
            'assets_container' => 'assets',
            'figma_api_base_url' => 'http://figma.test/v1/',
        ]]);

        AssetContainer::shouldReceive('all')->once()->andReturn(collect());
        AssetContainer::shouldReceive('findByHandle')->once()->with('assets')
            ->andReturn(Mockery::mock(AssetContainerContract::class));

        Http::fake([
            'http://figma.test/*' => Http::response(['document' => ['children' => []]]),
        ]);

        app(Controller::class)->info(0);

        Http::assertSent(fn ($request) => $request->url() === 'http://figma.test/v1/files/test-file');
    }

    public function test_package_configuration_can_be_cached(): void
    {
        $this->artisan('config:cache')->assertExitCode(0);
    }
}
