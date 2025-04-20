<?php

/**
 * Figma Asset Configurations
 *
 * You can define multiple asset configurations here. Each configuration maps to a specific
 * Figma export routine (e.g., SVGs, JPGs, PNGs, etc.) with its own settings.
 *
 * Example:
 * [
 *     'title' => 'SVG Icons',
 *     'token' => env('FIGMA_TOKEN'),
 *     'file_id' => env('FIGMA_FILE_ID'),
 *     'page_title' => 'Marketing Assets',
 *     'frame_title' => 'Logos',
 *     'assets_container' => 'assets',
 *     'format' => 'svg',
 *     'scale' => 1,
 *     'export_children' => true,
 *     'optimize_variant_names' => true,
 *     'figma_batch_size' => 100,
 *     'download_batch_size' => 15,
 *     'assets_transformer' => null,
 *     'before_upload' => null,
 * ]
 */

return [
    [
        /*
        |--------------------------------------------------------------------------
        | title
        |--------------------------------------------------------------------------
        |
        | A human-readable label to identify this configuration in the control panel.
        |
        | Example: 'SVG Icons'
        |
        */
        'title' => env('FIGMA_ASSETS_TITLE', 'My Configuration'),

        /*
        |--------------------------------------------------------------------------
        | token
        |--------------------------------------------------------------------------
        |
        | Your Figma API token for authentication. Use environment variables
        | for security: FIGMA_TOKEN=fig_your-token
        |
        */
        'token' => env('FIGMA_TOKEN', null),

        /*
        |--------------------------------------------------------------------------
        | file_id
        |--------------------------------------------------------------------------
        |
        | The Figma file ID where your design assets are located.
        |
        | Example: 'abc123xyz'
        |
        */
        'file_id' => env('FIGMA_FILE_ID', null),

        /*
        |--------------------------------------------------------------------------
        | page_title
        |--------------------------------------------------------------------------
        |
        | The title of the page within your Figma file to target for export.
        |
        | Example: 'Marketing Assets'
        |
        */
        'page_title' => env('FIGMA_PAGE_TITLE', null),

        /*
        |--------------------------------------------------------------------------
        | frame_title
        |--------------------------------------------------------------------------
        |
        | The title of the frame within the Figma page to target. Optional.
        |
        | Example: 'Logos'
        |
        */
        'frame_title' => env('FIGMA_FRAME_TITLE', null),

        /*
        |--------------------------------------------------------------------------
        | assets_container
        |--------------------------------------------------------------------------
        |
        | The Statamic asset container handle where exported files should be stored.
        |
        | Default: The first asset container.
        |
        */
        'assets_container' => env('FIGMA_ASSETS_CONTAINER', null),

        /*
        |--------------------------------------------------------------------------
        | format
        |--------------------------------------------------------------------------
        |
        | The format to export the Figma layers as.
        |
        | Options: 'svg', 'png', 'jpg', 'pdf'
        |
        | Default: 'svg'
        |
        */
        'format' => env('FIGMA_FORMAT', 'svg'),

        /*
        |--------------------------------------------------------------------------
        | scale
        |--------------------------------------------------------------------------
        |
        | The scale factor to apply to raster images (PNG, JPG).
        |
        | Example: 1, 2, 0.5
        |
        | Default: 1
        |
        */
        'scale' => env('FIGMA_SCALE', 1),

        /*
        |--------------------------------------------------------------------------
        | export_children
        |--------------------------------------------------------------------------
        |
        | If true, exports all nested layers inside a targeted frame.
        | Useful for components with variants inside frames.
        |
        | Default: false
        |
        */
        'export_children' => env('FIGMA_EXPORT_CHILDREN', false),

        /*
        |--------------------------------------------------------------------------
        | optimize_variant_names
        |--------------------------------------------------------------------------
        |
        | If true, trims variant names for cleaner asset filenames.
        |
        | Example: `button/type=primary--state=active` → `button/primary-active`
        |
        | Default: false
        |
        */
        'optimize_variant_names' => env('FIGMA_OPTIMIZE_VARIANT_NAMES', false),

        /*
        |--------------------------------------------------------------------------
        | figma_batch_size
        |--------------------------------------------------------------------------
        |
        | How many assets to request from the Figma API in a single batch.
        | Figma allows batching up to 100 items per request.
        |
        | Default: 100
        |
        */
        'figma_batch_size' => env('FIGMA_BATCH_SIZE', 100),

        /*
        |--------------------------------------------------------------------------
        | download_batch_size
        |--------------------------------------------------------------------------
        |
        | How many images should be downloaded in parallel when fetching asset URLs.
        | This controls HTTP pooling concurrency for downloading.
        |
        | Default: 15 (Good balance between performance and resource usage)
        |
        */
        'download_batch_size' => env('FIGMA_DOWNLOAD_BATCH_SIZE', 15),

        /*
        |--------------------------------------------------------------------------
        | assets_transformer
        |--------------------------------------------------------------------------
        |
        | Optional callable callback to transform asset metadata
        | before the assets are requested. Useful for custom
        | filtering or name modifications.
        |
        | Example: See README.md
        |
        | Default: null
        |
        */
        // 'assets_transformer' => fn($assets) => $assets,

        /*
        |--------------------------------------------------------------------------
        | before_upload
        |--------------------------------------------------------------------------
        |
        | Optional callback to process asset files before upload.
        | Useful for file optimization (e.g. via SVGO).
        | Receives a path and should return a path.
        |
        | Example: See README.md
        |
        | Default: null
        |
        */
        // 'before_upload' => fn($path) => $path,
    ],
];
