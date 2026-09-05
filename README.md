# Statamic Figma Assets

> Statamic Figma Assets is an addon that allows you to import assets from Figma into your Statamic site.

## Features

-   🎨 **Import** icons, illustrations, photos, and other assets from Figma
-   🗂️ **Flexible configuration** for asset container, format, size, and more
-   🔄 **"Smart" Importing** – import all assets or only new ones
-   🏎️ **Optimized performance** with batched generation pooled downloads
-   🛠️ **Transformers** for fine-grained filtering and naming of assets
-   ♾️ **Unlimited configurations** for multiple Figma files, asset containers, formats etc.

## Setup

You can install this addon via Composer:

```bash
composer require mariohamann/statamic-figma-assets
```

Once installed, head over to `Utilities > Figma Assets` in the Statamic control panel to start importing.

The compiled Control Panel assets are included in each release and published automatically by Statamic. Consumers do not need Node or an asset build step. To re-publish them after deployment, run:

```bash
php artisan vendor:publish --tag=statamic-figma-assets --force
```

Development, tests, and release procedures are in [CONTRIBUTING.md](CONTRIBUTING.md).

## Configuration

### Quick setup (via .env)

If you're fine with using the defaults, it's enough to set some configuration values in your `.env` file, for example:

```dotenv
FIGMA_TOKEN=fig_token-here
FIGMA_API_BASE_URL=https://api.figma.com/v1
FIGMA_FILE_ID=file-id-here
FIGMA_PAGE_TITLE="🎉 Assets"
FIGMA_FRAME_TITLE="Components"
FIGMA_ASSETS_CONTAINER="assets"
FIGMA_FORMAT="svg"
FIGMA_SCALE=1
FIGMA_EXPORT_CHILDREN=true
FIGMA_OPTIMIZE_VARIANT_NAMES=true
FIGMA_BATCH_SIZE=100
FIGMA_DOWNLOAD_BATCH_SIZE=15
```

### Advanced setup

For more advanced configuration and multiple configurations, you can publish the config file:

```bash
php artisan vendor:publish --provider="MarioHamann\StatamicFigmaAssets\ServiceProvider" --tag="config"
```

This will create a `config/statamic-figma-assets.php` file where you can set the default values for the addon.

From there, you can define multiple configurations and tweak every option to your needs.

### Example configuration

```php
return [
    [
        'title' => 'SVG Icons',
        'token' => env('FIGMA_TOKEN'),
        'figma_api_base_url' => env('FIGMA_API_BASE_URL', 'https://api.figma.com/v1'),
        'file_id' => env('FIGMA_FILE_ID'),
        'page_title' => 'Marketing Assets',
        'frame_title' => 'Logos',
        'assets_container' => 'assets',
        'format' => 'svg',
        'scale' => 1,
        'export_children' => true,
        'optimize_variant_names' => true,
        'assets_transformer' => null,
        'figma_batch_size' => 100,
        'download_batch_size' => 15,
    ]
    // ... more configurations
]
```

### Asset transformer

`assets_transformer` is an optional class name implementing `MarioHamann\StatamicFigmaAssets\Contracts\AssetsTransformer`. Laravel resolves it from the container, so it may use dependency injection and remains compatible with `php artisan config:cache`.

```php
return [
    [
        // ... other configuration options
        'assets_transformer' => App\Figma\FilterPrivateAssets::class,
    ],
];
```

```php
namespace App\Figma;

use MarioHamann\StatamicFigmaAssets\Contracts\AssetsTransformer;

class FilterPrivateAssets implements AssetsTransformer
{
    public function transform(array $assets): array
    {
        return array_values(array_filter(
            $assets,
            fn (array $asset) => ! preg_match('/(^_|\/_)/', $asset['name'])
        ));
    }
}
```

### Before-upload processor

`before_upload` is an optional class name implementing `MarioHamann\StatamicFigmaAssets\Contracts\BeforeUploadProcessor`. It receives the temporary file path and must return an existing file path.

For example, an SVG processor may invoke SVGO:

```php
return [
    [
        // ... other configuration options
        'before_upload' => App\Figma\OptimizeSvg::class,
    ],
];
```

```php
namespace App\Figma;

use MarioHamann\StatamicFigmaAssets\Contracts\BeforeUploadProcessor;

class OptimizeSvg implements BeforeUploadProcessor
{
    public function process(string $path): string
    {
        exec('svgo ' . escapeshellarg($path));

        return $path;
    }
}
```

Closures are not supported in the configuration because Laravel cannot cache a configuration file containing closures. Run `php artisan config:cache` after configuring the addon to verify your deployment configuration.
