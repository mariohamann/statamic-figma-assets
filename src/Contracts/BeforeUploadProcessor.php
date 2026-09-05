<?php

namespace MarioHamann\StatamicFigmaAssets\Contracts;

interface BeforeUploadProcessor
{
    public function process(string $path): string;
}