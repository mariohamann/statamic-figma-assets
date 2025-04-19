<?php

namespace MarioHamann\StatamicFigmaAssets\Tests;

use MarioHamann\StatamicFigmaAssets\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
