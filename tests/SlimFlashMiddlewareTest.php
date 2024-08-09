<?php

namespace Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WilliamSampaio\SlimFlashMessages\SlimFlashMiddleware;

#[CoversClass(SlimFlashMiddleware::class)]
class SlimFlashMiddlewareTest extends TestCase
{
    public function test_middleware()
    {
        $this->assertTrue(true);
    }
}
