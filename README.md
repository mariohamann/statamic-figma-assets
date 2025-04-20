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

## Configuration

### Quick setup (via .env)

If you're fine with using the defaults, it's enough to set some configuration values in your `.env` file, for example:

```dotenv
FIGMA_TOKEN=fig_token-here
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

### Example `assets_transformer`

The `assets_transformer` is a callable that allows you to filter and rename assets before they are fetched from Figma. It receives the asset data as an array and should return the modified data.

Here's an example that filters out assets starting with an underscore (e. g. `_icon` or `icons/_icon`):

```php
return [
    [
        // ... other configuration options
        'assets_transformer' => function ($assets) {
            $assets = array_filter(
                $assets,
                fn($asset) => !preg_match('/(^_|\/_)/', $asset['name'])
            );
            return $assets;
        },
    ],
];

```

### Example `before_upload`

The `before_upload` callback allows you to modify the asset before it is uploaded to the Statamic assets container. This can be useful for optimizing images or performing other transformations.

Here we're using it to optimize SVG files using SVGO. If you want to use this, ensure to use the correct path to SVGO (e. g. via `which svgo`).

```php
return [
    [
        // ... other configuration options
        'before_upload' => function ($path) {
            exec("~/Library/pnpm/svgo $path");
            return $path;
        },
    ],
];
```
