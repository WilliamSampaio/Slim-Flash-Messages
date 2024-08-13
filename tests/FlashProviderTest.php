<?php declare(strict_types=1);

namespace SlimFlashMessages\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Slim\Factory\ServerRequestCreatorFactory;
use SlimFlashMessages\FlashProvider;
use InvalidArgumentException;
use RuntimeException;

#[CoversClass(FlashProvider::class)]
class FlashProviderTest extends TestCase
{
    private array $storage;
    private string $storageKey;
    private FlashProvider $flash_provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = [];
        $this->storageKey = 'test_storage_key';
        $this->flash_provider = new FlashProvider($this->storage, $this->storageKey);
    }

    public function test_storage_is_valid()
    {
        $this->assertInstanceOf(FlashProvider::class, $this->flash_provider);
    }

    public function test_storagekey_is_valid()
    {
        $this->assertArrayHasKey($this->storageKey, $this->storage);
    }

    public function test_storage_session()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        new FlashProvider();
        $this->assertArrayHasKey('__flash', $_SESSION);
        session_unset();
    }

    public function test_storage_is_null_and_session_not_is_set()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $this->expectException(RuntimeException::class);
        new FlashProvider();
    }

    public function test_storage_invalid_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $storage = false;
        new FlashProvider($storage);
    }

    public function test_add_expect_invalid_key()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->flash_provider->add('', 'oi!');
    }

    public function test_add_expect_invalid_data()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->flash_provider->add('test', 0);
    }

    public function test_add_messages()
    {
        $this->flash_provider->add('test', 'Hello');
        $this->flash_provider->add('test', ['type' => 'error', 'text' => 'Error!']);
        $this->flash_provider->add('test', 12345);
        $this->assertCount(3, $this->storage[$this->storageKey]['test']);
    }

    public function test_getall_function()
    {
        $this->assertIsArray($this->flash_provider->getAll());
    }

    public function test_get_function()
    {
        $this->flash_provider->add('test', 'Hello');
        $this->assertIsArray($this->flash_provider->get('test'));
        $this->assertNull($this->flash_provider->get('test_'));
    }

    public function test_get_first_function()
    {
        $this->assertNull($this->flash_provider->get_first('test'));
        $this->flash_provider->add('test', 'Hello');
        $this->assertEquals('Hello', $this->flash_provider->get_first('test'));
    }

    public function test_get_first_remove_function()
    {
        $this->flash_provider->add('test', 'Hello');
        $this->flash_provider->add('test', 'World');
        $this->assertEquals('Hello', $this->flash_provider->get_first('test', true));
        $this->assertEquals('World', $this->flash_provider->get_first('test', true));
    }

    public function test_get_last_function()
    {
        $this->assertNull($this->flash_provider->get_last('test'));
        $this->flash_provider->add('test', 'Hello');
        $this->flash_provider->add('test', 'World');
        $this->assertEquals('World', $this->flash_provider->get_last('test'));
    }

    public function test_get_last_remove_function()
    {
        $this->flash_provider->add('test', 'Hello');
        $this->flash_provider->add('test', 'World');
        $this->assertEquals('World', $this->flash_provider->get_last('test', true));
        $this->assertEquals('Hello', $this->flash_provider->get_last('test', true));
    }

    public function test_has_function()
    {
        $this->flash_provider->add('test', 'Hello');
        $this->assertTrue($this->flash_provider->has('test'));
    }

    public function test_clearall_function()
    {
        $this->flash_provider->add('test_1', 'Hello');
        $this->flash_provider->add('test_2', 'World');
        $this->flash_provider->clearAll();
        $this->assertIsArray($this->storage[$this->storageKey]);
        $this->assertEmpty($this->storage[$this->storageKey]);
    }

    public function test_clear_function()
    {
        $this->flash_provider->add('test_1', 'Hello');
        $this->flash_provider->add('test_2', 'World');
        $this->flash_provider->clear('test_1');
        $this->assertCount(1, $this->storage[$this->storageKey]);
        $this->assertArrayHasKey('test_2', $this->storage[$this->storageKey]);
    }

    public function test_fromrequest_function()
    {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();
        $request = $request->withAttribute('__flash', $this->flash_provider);
        $this->assertInstanceOf(FlashProvider::class, $this->flash_provider::fromRequest($request));
    }

    public function test_fromrequest_exception()
    {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();
        $this->expectException(RuntimeException::class);
        $this->flash_provider::fromRequest($request);
    }
}
