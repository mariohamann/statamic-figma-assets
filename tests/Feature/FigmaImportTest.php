<?php

namespace MarioHamann\StatamicFigmaAssets\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use MarioHamann\StatamicFigmaAssets\Controller;
use MarioHamann\StatamicFigmaAssets\Tests\TestCase;
use MarioHamann\StatamicFigmaAssets\Tests\Support\FilterCheckAsset;
use MarioHamann\StatamicFigmaAssets\Tests\Support\ReplaceSvgContent;

class FigmaImportTest extends TestCase
{
    private string $downloadedAsset;

    public function test_import_writes_a_figma_asset_to_a_real_statamic_container(): void
    {
        $this->configureFigmaImport();
        $this->fakeFigma();

        app(Controller::class)->import(0);

        Storage::disk('figma-assets')->assertExists('icons/check.svg');
        $this->assertSame($this->fixture('check.svg'), Storage::disk('figma-assets')->get('icons/check.svg'));
    }

    public function test_reimport_replaces_an_existing_asset(): void
    {
        $this->configureFigmaImport();
        $this->fakeFigma();
        app(Controller::class)->import(0);

        $updatedSvg = '<svg><path d="M0 0"/></svg>';
        $this->downloadedAsset = $updatedSvg;
        app(Controller::class)->reimport(0);

        $this->assertSame($updatedSvg, Storage::disk('figma-assets')->get('icons/check.svg'));
    }

    public function test_assets_transformer_is_resolved_from_its_configured_class(): void
    {
        $this->configureFigmaImport(['assets_transformer' => FilterCheckAsset::class]);
        $this->fakeFigma();

        app(Controller::class)->import(0);

        Storage::disk('figma-assets')->assertMissing('icons/check.svg');
    }

    public function test_before_upload_processor_is_resolved_from_its_configured_class(): void
    {
        $this->configureFigmaImport(['before_upload' => ReplaceSvgContent::class]);
        $this->fakeFigma();

        app(Controller::class)->import(0);

        $this->assertSame('<svg><path d="M1 1"/></svg>', Storage::disk('figma-assets')->get('icons/check.svg'));
    }

    public function test_invalid_transformer_configuration_fails_with_a_clear_error(): void
    {
        $this->configureFigmaImport(['assets_transformer' => \stdClass::class]);
        $this->fakeFigma();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must implement ' . \MarioHamann\StatamicFigmaAssets\Contracts\AssetsTransformer::class);

        app(Controller::class)->import(0);
    }

    public function test_info_reports_the_available_figma_asset_count(): void
    {
        $this->configureFigmaImport();
        $this->fakeFigma();

        app(Controller::class)->info(0);

        $this->assertSame(
            'There are 1 assets available in Figma. (0 already exist in Statamic.)',
            session('success')
        );
    }

    public function test_figma_api_failure_is_returned_as_feedback(): void
    {
        $this->configureFigmaImport();
        Http::fake([
            'http://figma.test/v1/files/test-file' => Http::response(['error' => 'Unauthorized'], 401),
        ]);

        app(Controller::class)->info(0);

        $this->assertSame('Figma API Error: {"error":"Unauthorized"}', session('error'));
    }

    public function test_missing_figma_page_is_returned_as_feedback(): void
    {
        $this->configureFigmaImport(['page_title' => 'Missing page']);
        $this->fakeFigma();

        app(Controller::class)->info(0);

        $this->assertSame('Cannot find page "Missing page"', session('error'));
    }

    public function test_progress_returns_the_cached_message(): void
    {
        $this->configureFigmaImport();
        $config = config('statamic-figma-assets.0');
        cache()->put('figma_progress_' . md5(json_encode(array_merge([
            'assets_container' => 'figma-assets',
            'title' => null,
            'token' => null,
            'figma_api_base_url' => 'https://api.figma.com/v1',
            'file_id' => null,
            'page_title' => null,
            'frame_title' => null,
            'format' => 'svg',
            'scale' => 1,
            'export_children' => true,
            'optimize_variant_names' => true,
            'figma_batch_size' => 100,
            'download_batch_size' => 15,
            'assets_transformer' => null,
            'before_upload' => null,
        ], $config))), 'Imported 1/1');

        $response = app(Controller::class)->progress(0);

        $this->assertSame(['message' => 'Imported 1/1'], $response->getData(true));
    }

    private function configureFigmaImport(array $overrides = []): void
    {
        config()->set('statamic-figma-assets', [array_merge([
            'title' => 'Test assets',
            'token' => 'test-token',
            'figma_api_base_url' => 'http://figma.test/v1',
            'file_id' => 'test-file',
            'page_title' => 'Exports',
            'assets_container' => 'figma-assets',
            'format' => 'svg',
            'export_children' => false,
        ], $overrides)]);
    }

    private function fakeFigma(): void
    {
        $this->downloadedAsset = $this->fixture('check.svg');

        Http::fake([
            'http://figma.test/v1/files/test-file' => Http::response(json_decode($this->fixture('figma-file.json'), true)),
            'http://figma.test/v1/images/test-file*' => Http::response([
                'images' => ['icon-check' => 'http://figma.test/downloads/check.svg'],
            ]),
            'http://figma.test/downloads/check.svg' => fn () => Http::response($this->downloadedAsset),
        ]);
    }

    private function fixture(string $name): string
    {
        return file_get_contents(__DIR__ . '/../Fixtures/' . $name);
    }
}