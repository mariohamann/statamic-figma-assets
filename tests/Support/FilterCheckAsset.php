<?php

namespace MarioHamann\StatamicFigmaAssets\Tests\Support;

use MarioHamann\StatamicFigmaAssets\Contracts\AssetsTransformer;

class FilterCheckAsset implements AssetsTransformer
{
    public function transform(array $assets): array
    {
        return array_values(array_filter($assets, fn ($asset) => $asset['name'] !== 'icons/check'));
    }
}