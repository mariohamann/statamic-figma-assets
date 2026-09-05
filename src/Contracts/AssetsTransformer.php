<?php

namespace MarioHamann\StatamicFigmaAssets\Contracts;

interface AssetsTransformer
{
    public function transform(array $assets): array;
}