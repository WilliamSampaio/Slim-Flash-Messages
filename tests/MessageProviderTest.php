<?php

namespace Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WilliamSampaio\SlimFlashMessages\MessageProvider;

#[CoversClass(MessageProvider::class)]
class MessageProviderTest extends TestCase
{
    public function test_storage()
    {
        $this->assertTrue(true);
    }
}
