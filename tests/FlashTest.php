<?php

namespace Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WilliamSampaio\SlimFlashMessages\Flash;
use WilliamSampaio\SlimFlashMessages\FlashProvider;

#[CoversClass(Flash::class)]
#[UsesClass(FlashProvider::class)]
class FlashTest extends TestCase
{
    public function test_get_instance()
    {
        $storage = [];
        $this->assertInstanceOf(FlashProvider::class, Flash::getInstance($storage));
    }

    public function test_construct()
    {
        $this->expectException(RuntimeException::class);
        new Flash;
    }
}
