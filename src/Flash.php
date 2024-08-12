<?php declare(strict_types=1);

namespace SlimFlashMessages;

use RuntimeException;

final class Flash
{
    private static ?FlashProvider $instance = null;

    public function __construct()
    {
        throw new RuntimeException('Not allowed to instantiate.');
    }

    public static function getInstance(&$storage = null, $storageKey = null): FlashProvider
    {
        if (self::$instance === null) {
            self::$instance = new FlashProvider($storage, $storageKey);
        }
        return self::$instance;
    }
}
