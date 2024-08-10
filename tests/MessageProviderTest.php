<?php

namespace Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Slim\Factory\ServerRequestCreatorFactory;
use WilliamSampaio\SlimFlashMessages\MessageProvider;

#[CoversClass(MessageProvider::class)]
class MessageProviderTest extends TestCase
{
    private array $storage;
    private MessageProvider $messageProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = [];
        $this->messageProvider = new MessageProvider($this->storage);
    }

    public function test_storage_valid()
    {
        $this->assertArrayHasKey('slim_flash_messages', $this->storage);
    }

    public function test_storage_is_null_exception()
    {
        $this->expectException(RuntimeException::class);
        $storage = null;
        new MessageProvider($storage);
    }

    public function test_storage_session()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        new MessageProvider();
        $this->assertArrayHasKey('slim_flash_messages', $_SESSION);
        session_unset();
    }

    public function test_storage_invalid_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $storage = false;
        new MessageProvider($storage);
    }

    public function test_storagekey_is_valid()
    {
        $storage = [];
        new MessageProvider($storage, 'test');
        $this->assertArrayHasKey('test', $storage);
    }

    public function test_add_expect_invalid_key()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->messageProvider->add(false, 'oi!');
    }

    public function test_add_expect_invalid_data()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->messageProvider->add('test', 0);
    }

    public function test_add_messages()
    {
        $this->messageProvider->add('test', 'Hello');
        $this->messageProvider->add('test', ['type' => 'error', 'text' => 'Error!']);
        $this->messageProvider->add('test', 12345);
        $this->assertCount(3, $this->storage['slim_flash_messages']['test']);
    }

    public function test_getall_function()
    {
        $this->assertIsArray($this->messageProvider->getAll());
    }

    public function test_get_function()
    {
        $this->messageProvider->add('test', 'Hello');
        $this->assertIsArray($this->messageProvider->get('test'));
        $this->assertNull($this->messageProvider->get('test_'));
    }

    public function test_getfirst_function()
    {
        $this->assertNull($this->messageProvider->getFirst('test'));
        $this->messageProvider->add('test', 'Hello');
        $this->assertEquals('Hello', $this->messageProvider->getFirst('test'));
    }

    public function test_getfirst_remove_function()
    {
        $this->messageProvider->add('test', 'Hello');
        $this->messageProvider->add('test', 'World');
        $this->assertEquals('Hello', $this->messageProvider->getFirst('test', true));
        $this->assertEquals('World', $this->messageProvider->getFirst('test', true));
    }

    public function test_getlast_function()
    {
        $this->assertNull($this->messageProvider->getLast('test'));
        $this->messageProvider->add('test', 'Hello');
        $this->messageProvider->add('test', 'World');
        $this->assertEquals('World', $this->messageProvider->getLast('test'));
    }

    public function test_getlast_remove_function()
    {
        $this->messageProvider->add('test', 'Hello');
        $this->messageProvider->add('test', 'World');
        $this->assertEquals('World', $this->messageProvider->getLast('test', true));
        $this->assertEquals('Hello', $this->messageProvider->getLast('test', true));
    }

    public function test_has_function()
    {
        $this->messageProvider->add('test', 'Hello');
        $this->assertTrue($this->messageProvider->has('test'));
    }

    public function test_clearall_function()
    {
        $this->messageProvider->add('test_1', 'Hello');
        $this->messageProvider->add('test_2', 'World');
        $this->messageProvider->clearAll();
        $this->assertIsArray($this->storage['slim_flash_messages']);
        $this->assertEmpty($this->storage['slim_flash_messages']);
    }

    public function test_clear_function()
    {
        $this->messageProvider->add('test_1', 'Hello');
        $this->messageProvider->add('test_2', 'World');
        $this->messageProvider->clear('test_1');
        $this->assertCount(1, $this->storage['slim_flash_messages']);
        $this->assertArrayHasKey('test_2', $this->storage['slim_flash_messages']);
    }

    public function test_fromrequest_function()
    {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();
        $request = $request->withAttribute('slim_flash_messages', $this->messageProvider);
        $this->assertInstanceOf(MessageProvider::class, $this->messageProvider::fromRequest($request));
    }

    public function test_fromrequest_exception()
    {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();
        $request = $request->withAttribute('test', $this->messageProvider);
        $this->expectException(RuntimeException::class);
        $this->messageProvider::fromRequest($request);
    }
}
