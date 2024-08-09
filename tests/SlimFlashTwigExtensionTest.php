<?php

namespace Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WilliamSampaio\SlimFlashMessages\SlimFlashTwigExtension;

#[CoversClass(SlimFlashTwigExtension::class)]
class SlimFlashTwigExtensionTest extends TestCase
{
    public function test_extension()
    {
        $this->assertTrue(true);
    }
}
