<?php

namespace MarioHamann\StatamicFigmaAssets\Tests\Support;

use MarioHamann\StatamicFigmaAssets\Contracts\BeforeUploadProcessor;

class ReplaceSvgContent implements BeforeUploadProcessor
{
    public function process(string $path): string
    {
        file_put_contents($path, '<svg><path d="M1 1"/></svg>');

        return $path;
    }
}