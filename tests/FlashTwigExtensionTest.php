<?php declare(strict_types=1);

namespace Tests;

use DI\Container;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\App;
use Twig\TwigFunction;
use WilliamSampaio\SlimFlashMessages\FlashProvider;
use WilliamSampaio\SlimFlashMessages\FlashTwigExtension;
use RuntimeException;

#[CoversClass(FlashTwigExtension::class)]
#[UsesClass(FlashProvider::class)]
class FlashTwigExtensionTest extends TestCase
{
    private array $storage;
    private FlashProvider $flash_provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = [];
        $this->flash_provider = new FlashProvider($this->storage);
    }

    public function test_construct()
    {
        $this->assertInstanceOf(
            FlashTwigExtension::class,
            new FlashTwigExtension($this->flash_provider)
        );
    }

    public function test_create_from_container()
    {
        $container = new Container();
        $container->set('flash', $this->flash_provider);
        $app = new App(new ResponseFactory, $container);
        $this->assertInstanceOf(
            FlashTwigExtension::class,
            FlashTwigExtension::createFromContainer($app, 'flash')
        );
    }

    public function test_create_from_container_null_container_exception()
    {
        $app = new App(new ResponseFactory);
        $this->expectException(RuntimeException::class);
        FlashTwigExtension::createFromContainer($app);
    }

    public function test_create_from_container_containerkey_does_not_exist()
    {
        $container = new Container();
        $container->set('flash', $this->flash_provider);
        $app = new App(new ResponseFactory, $container);
        $this->expectException(RuntimeException::class);
        FlashTwigExtension::createFromContainer($app, 'flash_');
    }

    public function test_create_from_container_invalid_instance_of()
    {
        $container = new Container();
        $container->set('flash', []);
        $app = new App(new ResponseFactory, $container);
        $this->expectException(RuntimeException::class);
        FlashTwigExtension::createFromContainer($app, 'flash');
    }

    public function test_get_messages()
    {
        $this->flash_provider->add('teste', 'Hello World!');
        $ext = new FlashTwigExtension($this->flash_provider);
        $messages = $ext->get_messages('teste');
        $this->assertEquals([0 => 'Hello World!'], $messages);
    }

    public function test_get_messages_all()
    {
        $this->flash_provider->add('teste_1', 'Hello World!');
        $this->flash_provider->add('teste_2', 'Hello World!');
        $ext = new FlashTwigExtension($this->flash_provider);
        $messages = $ext->get_messages();
        $this->assertEquals([
            'teste_1' => [0 => 'Hello World!'],
            'teste_2' => [0 => 'Hello World!'],
        ], $messages);
    }

    public function test_get_first()
    {
        $this->flash_provider->add('teste', 'Hello World!');
        $this->flash_provider->add('teste', 'Hello World! 2');
        $ext = new FlashTwigExtension($this->flash_provider);
        $message = $ext->get_first('teste');
        $this->assertEquals('Hello World!', $message);
    }

    public function test_get_last()
    {
        $this->flash_provider->add('teste', 'Hello World!');
        $this->flash_provider->add('teste', 'Hello World! 2');
        $ext = new FlashTwigExtension($this->flash_provider);
        $message = $ext->get_last('teste');
        $this->assertEquals('Hello World! 2', $message);
    }

    public function test_getfunctions()
    {
        $ext = new FlashTwigExtension($this->flash_provider);
        $this->assertContainsOnlyInstancesOf(TwigFunction::class, $ext->getFunctions());
    }
}
