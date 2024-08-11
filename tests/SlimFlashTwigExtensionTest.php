<?php

namespace Tests;

use DI\Container;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Slim\App;
use Slim\Psr7\Factory\ResponseFactory;
use Twig\TwigFunction;
use WilliamSampaio\SlimFlashMessages\MessageProvider;
use WilliamSampaio\SlimFlashMessages\SlimFlashTwigExtension;

#[CoversClass(SlimFlashTwigExtension::class)]
#[UsesClass(MessageProvider::class)]
class SlimFlashTwigExtensionTest extends TestCase
{
    private array $storage;
    private MessageProvider $messageProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = [];
        $this->messageProvider = new MessageProvider($this->storage);
    }

    public function test_create()
    {
        $this->assertInstanceOf(
            SlimFlashTwigExtension::class,
            new SlimFlashTwigExtension($this->messageProvider)
        );
    }

    public function test_createfromcontainer()
    {
        $container = new Container();
        $container->set('flash', $this->messageProvider);
        $app = new App(new ResponseFactory, $container);
        $this->assertInstanceOf(
            SlimFlashTwigExtension::class,
            SlimFlashTwigExtension::createFromContainer($app, 'flash')
        );
    }

    public function test_createfromcontainer_null_container()
    {
        $app = new App(new ResponseFactory);
        $this->expectException(RuntimeException::class);
        SlimFlashTwigExtension::createFromContainer($app);
    }

    public function test_createfromcontainer_containerkey_does_not_exist()
    {
        $container = new Container();
        $container->set('flash', $this->messageProvider);
        $app = new App(new ResponseFactory, $container);
        $this->expectException(RuntimeException::class);
        SlimFlashTwigExtension::createFromContainer($app, 'flash_');
    }

    public function test_createfromcontainer_invalid_instance_of()
    {
        $container = new Container();
        $container->set('flash', []);
        $app = new App(new ResponseFactory, $container);
        $this->expectException(RuntimeException::class);
        SlimFlashTwigExtension::createFromContainer($app, 'flash');
    }

    public function test_get_messages()
    {
        $this->messageProvider->add('teste', 'Hello World!');
        $ext = new SlimFlashTwigExtension($this->messageProvider);
        $messages = $ext->get_messages('teste');
        $this->assertEquals([0 => 'Hello World!'], $messages);
    }

    public function test_get_messages_all()
    {
        $this->messageProvider->add('teste_1', 'Hello World!');
        $this->messageProvider->add('teste_2', 'Hello World!');
        $ext = new SlimFlashTwigExtension($this->messageProvider);
        $messages = $ext->get_messages();
        $this->assertEquals([
            'teste_1' => [0 => 'Hello World!'],
            'teste_2' => [0 => 'Hello World!'],
        ], $messages);
    }

    public function test_get_first()
    {
        $this->messageProvider->add('teste', 'Hello World!');
        $this->messageProvider->add('teste', 'Hello World! 2');
        $ext = new SlimFlashTwigExtension($this->messageProvider);
        $message = $ext->get_first('teste');
        $this->assertEquals('Hello World!', $message);
    }

    public function test_get_last()
    {
        $this->messageProvider->add('teste', 'Hello World!');
        $this->messageProvider->add('teste', 'Hello World! 2');
        $ext = new SlimFlashTwigExtension($this->messageProvider);
        $message = $ext->get_last('teste');
        $this->assertEquals('Hello World! 2', $message);
    }

    public function test_getfunctions()
    {
        $ext = new SlimFlashTwigExtension($this->messageProvider);
        $this->assertContainsOnlyInstancesOf(TwigFunction::class, $ext->getFunctions());
    }
}
