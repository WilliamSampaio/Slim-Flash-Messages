<?php declare(strict_types=1);

namespace SlimFlashMessages\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SlimFlashMessages\Flash;
use SlimFlashMessages\FlashProvider;
use RuntimeException;

#[CoversClass(Flash::class)]
#[UsesClass(FlashProvider::class)]
class FlashTest extends TestCase
{
    public function test_get_instance()
    {
        $storage = [];
        $this->assertInstanceOf(FlashProvider::class, Flash::getInstance($storage));
    }

    public function test_get_instance_session_not_active()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $this->assertInstanceOf(FlashProvider::class, Flash::getInstance());
    }

    public function test_construct()
    {
        $this->expectException(RuntimeException::class);
        new Flash;
    }
}
