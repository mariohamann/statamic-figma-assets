<?php

namespace MarioHamann\StatamicFigmaAssets\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use MarioHamann\StatamicFigmaAssets\Controller;
use MarioHamann\StatamicFigmaAssets\Tests\TestCase;

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

    private function configureFigmaImport(): void
    {
        config()->set('statamic-figma-assets', [[
            'title' => 'Test assets',
            'token' => 'test-token',
            'figma_api_base_url' => 'http://figma.test/v1',
            'file_id' => 'test-file',
            'page_title' => 'Exports',
            'assets_container' => 'figma-assets',
            'format' => 'svg',
            'export_children' => false,
        ]]);
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