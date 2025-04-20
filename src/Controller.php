<?php

namespace MarioHamann\StatamicFigmaAssets;

use Statamic\Facades\AssetContainer;
use Illuminate\Support\Facades\Http;
use Statamic\Facades\Asset;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Statamic\Events\AssetReuploaded;
use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{
    private $configs;

    public function __construct()
    {
        // Manually load the config file as otherwise both configs are merged
        $configPath = config_path('statamic-figma-assets.php');

        $config = file_exists($configPath)
            ? require $configPath
            : config('statamic-figma-assets');

        $this->configs = $this->getConfigDefaults($config);
    }

    public function getConfigDefaults($configs)
    {
        return collect($configs)->map(function ($config) {
            // Remove all nulls from the user-defined config
            $filteredConfig = array_filter($config, fn($value) => !is_null($value));

            return array_merge([
                'assets_container' => AssetContainer::all()->first()?->handle(),
                'title' => null,
                'token' => null,
                'file_id' => null,
                'page_title' => null,
                'frame_title' => null,
                'format' => 'svg',
                'scale' => 1,
                'export_children' => true,
                'optimize_variant_names' => true,
                'assets_transformer' => null,
                'figma_batch_size' => 100,
                'download_batch_size' => 15,
            ], $filteredConfig);
        })->toArray();
    }

    public function index()
    {
        return view('statamic-figma-assets::index', [
            'configs' => $this->configs,
        ]);
    }

    public function getAssetContainers()
    {
        return collect($this->configs)
            ->pluck('assets_container')
            ->map(fn($name) => AssetContainer::findByHandle($name));
    }

    public function info($configIndex)
    {
        $config = $this->configs[$configIndex];

        if (!AssetContainer::findByHandle($config['assets_container'])) {
            return back()->with('error', 'Container not found.');
        }

        $assets = $this->fetchFigmaAssets($configIndex);

        if ($assets instanceof \Illuminate\Http\RedirectResponse) {
            return $assets;
        }

        $countBefore = count($assets);
        $assets = $this->filterNonExistingAssets($assets, $config);
        $countSkipped = $countBefore - count($assets);

        return back()->with('success', "There are " . $countBefore . " assets available in Figma. (" . $countSkipped . " already exist in Statamic.)");
    }

    public function fetchFigmaAssets($configIndex)
    {

        $config = $this->configs[$configIndex];
        $response = $this->fetchFigmaFile($config);

        if ($response->failed()) {
            return back()->with('error', 'Figma API Error: ' . $response->body());
        }

        $assetsArray = $this->extractFrameAssets($response->json('document'), $config);

        if (!is_array($assetsArray)) return $assetsArray;

        $assets = $this->processAssetsArray($assetsArray, $config);
        $assets = $this->removeDuplicatesByKey($assets, 'name');
        $this->cleanUpAssetNames($assets, $config);

        return $this->applyArrayTransformer($assets, $config['assets_transformer']);
    }

    private function fetchFigmaFile(array $config)
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Figma-Token' => $config['token'],
        ])->get("https://api.figma.com/v1/files/{$config['file_id']}");
    }

    private function extractFrameAssets(array $document, array $config)
    {
        $page = collect($document['children'] ?? [])->firstWhere('name', $config['page_title']);

        if (!$page) {
            return back()->with('error', "Cannot find page \"{$config['page_title']}\"");
        }

        $assetsArray = $page['children'] ?? [];

        if (!empty($config['frame_title'])) {
            $frame = collect($assetsArray)->firstWhere('name', $config['frame_title']);
            if (!$frame) {
                return back()->with('error', "Cannot find frame \"{$config['frame_title']}\"");
            }
            return $frame['children'] ?? [];
        }

        return $assetsArray;
    }

    private function processAssetsArray(array $assetsArray, array $config): array
    {
        return collect($assetsArray)->flatMap(function ($asset) use ($config) {
            if (empty($config['export_children']) || empty($asset['children'])) {
                return [[
                    'id' => $asset['id'],
                    'name' => $asset['name'],
                ]];
            }

            return collect($asset['children'])->map(fn($child) => [
                'id' => $child['id'],
                'name' => "{$asset['name']}/" . implode('--', array_map('trim', explode(',', $child['name']))),
            ]);
        })->toArray();
    }

    private function cleanUpAssetNames(array &$assets, array $config): void
    {
        foreach ($assets as &$asset) {
            $asset['name'] = $config['optimize_variant_names'] ? $this->optimize_variant_names($asset['name']) : $asset['name'];
        }
    }

    private function applyArrayTransformer(array $assets, $assets_transformer)
    {
        return is_callable($assets_transformer) ? $assets_transformer($assets) : $assets;
    }

    private function removeDuplicatesByKey(array $items, string $key): array
    {
        $seen = [];
        return array_filter($items, function ($item) use (&$seen, $key) {
            if (in_array($item[$key], $seen)) {
                return false;
            }
            $seen[] = $item[$key];
            return true;
        });
    }

    public function import($configIndex)
    {
        return $this->importFigmaAssetsToStatamic($configIndex, false);
    }

    public function reimport($configIndex)
    {
        return $this->importFigmaAssetsToStatamic($configIndex, true);
    }

    public function importFigmaAssetsToStatamic($configIndex, $override)
    {
        $config = $this->configs[$configIndex];
        $assets = $this->fetchFigmaAssets($configIndex);

        if ($assets instanceof \Illuminate\Http\RedirectResponse) {
            return $assets;
        }

        if (!$override) {
            $countBefore = count($assets);
            $assets = $this->filterNonExistingAssets($assets, $config);
            $countSkipped = $countBefore - count($assets);
        }

        [$imported, $reimported] = $this->fetchAndUploadAssets($assets, $config);
        $this->clearProgressMessage($config);

        return back()->with(
            'success',
            !$override
                ? "Imported {$imported}, reimported {$reimported}, skipped {$countSkipped}."
                : "Imported {$imported}, reimported {$reimported}."
        );
    }

    private function filterNonExistingAssets(array $assets, array $config)
    {
        $existingAssets = Asset::query()
            ->where('container', $config['assets_container'])
            ->get();

        $existingPaths = $existingAssets->pluck('path')->toArray();

        $assets = array_filter($assets, function ($asset) use ($existingPaths, $config) {
            return !in_array($asset['name'] . '.' . $config['format'], $existingPaths);
        });

        return $assets;
    }

    private function fetchAndUploadAssets(array $assets, array $config): array
    {
        $total = count($assets);
        $imported = $reimported = 0;
        $mimeTypes = [
            'svg' => 'image/svg+xml',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
        ];

        $figmaBatchSize = $config['figma_batch_size'];
        $downloadBatchSize = $config['download_batch_size'];

        for ($i = 0; $i < count($assets); $i += $figmaBatchSize) {
            $figmaBatch = array_slice($assets, $i, $figmaBatchSize);
            $ids = implode(',', array_column($figmaBatch, 'id'));

            // Fetch image URLs from Figma
            $res = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Figma-Token' => $config['token'],
            ])->get("https://api.figma.com/v1/images/{$config['file_id']}?ids={$ids}&format={$config['format']}&scale={$config['scale']}");

            if ($res->failed()) continue;

            $images = $res->json('images');

            // Add URLs to assets
            foreach ($figmaBatch as $j => $asset) {
                $figmaBatch[$j]['url'] = $images[$asset['id']] ?? null;
            }

            // Now process smaller download batches
            for ($j = 0; $j < count($figmaBatch); $j += $downloadBatchSize) {
                $downloadBatch = array_slice($figmaBatch, $j, $downloadBatchSize);

                // Concurrent download
                $responses = Http::pool(
                    fn($pool) =>
                    collect($downloadBatch)->map(
                        fn($asset) =>
                        $asset['url'] ? $pool->get($asset['url']) : null
                    )->all()
                );

                foreach ($downloadBatch as $k => $asset) {
                    $response = $responses[$k] ?? null;
                    if (
                        empty($asset['url']) ||
                        !$response instanceof \Illuminate\Http\Client\Response ||
                        $response->failed()
                    ) {
                        continue;
                    }


                    try {
                        $fileContent = $responses[$k]->body();
                        if (!$fileContent) continue;

                        $path = $asset['name'] . '.' . $config['format'];
                        $existing = Asset::query()
                            ->where('container', $config['assets_container'])
                            ->where('path', $path)
                            ->first();

                        if ($existing) {
                            $this->reuploadAsset($existing, $fileContent);
                            $reimported++;
                        } else {
                            $tmpPath = tempnam(sys_get_temp_dir(), 'figma_') . '.' . $config['format'];
                            file_put_contents($tmpPath, $fileContent);

                            Asset::make()
                                ->container($config['assets_container'])
                                ->path($path)
                                ->data(['title' => $asset['name']])
                                ->upload(new UploadedFile($tmpPath, $path, $mimeTypes[$config['format']], null, true))
                                ->save();

                            unlink($tmpPath); // Clean up
                            $imported++;
                        }

                        $this->updateProgressMessage($config, "Imported " . ($imported + $reimported) . "/" . $total);
                    } catch (\Exception $e) {
                        logger()->error("Failed to import asset: {$asset['name']}", ['error' => $e->getMessage()]);
                    }
                }

                gc_collect_cycles();
            }
        }

        return [$imported, $reimported];
    }

    private function reuploadAsset($asset, $content): void
    {
        $asset->disk()->put($asset->path(), $content);
        $asset->meta = null;
        $asset->cacheStore()->forget($asset->metaCacheKey());
        $asset->writeMeta($asset->generateMeta());
        AssetReuploaded::dispatch($asset);
        $asset->save();
    }

    public function progress($configIndex)
    {
        $config = $this->configs[$configIndex] ?? null;

        $key = 'figma_progress_' . md5(json_encode($config));
        $message = cache()->get($key, 'Processing...');

        logger()->info(['message' => ($message)]);

        return response()
            ->json(['message' => ($message)])
            ->header('Content-Type', 'application/json')
            ->header('Access-Control-Allow-Origin', '*');
    }

    protected function updateProgressMessage(array $config, string $message): void
    {
        $key = 'figma_progress_' . md5(json_encode($config));
        cache()->put($key, $message, now()->addMinutes(10));
    }

    protected function clearProgressMessage(array $config): void
    {
        $key = 'figma_progress_' . md5(json_encode($config));
        cache()->forget($key);
    }

    static function optimize_variant_names(string $name): string
    {
        return collect(explode('/', $name))->map(function ($segment) {
            $parts = array_map(function ($sub) {
                return strpos($sub, '=') !== false ? explode('=', $sub)[1] : $sub;
            }, explode('--', $segment));
            return implode('-', $parts);
        })->implode('/');
    }
}
