<?php

namespace Tests;

use DI\Container;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use WilliamSampaio\SlimFlashMessages\MessageProvider;
use WilliamSampaio\SlimFlashMessages\SlimFlashMiddleware;

#[CoversClass(SlimFlashMiddleware::class)]
#[UsesClass(MessageProvider::class)]
class SlimFlashMiddlewareTest extends TestCase
{
    private array $storage;
    private MessageProvider $messageProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = [];
        $this->messageProvider = new MessageProvider($this->storage);
    }

    public function test_attributename()
    {
        $this->assertInstanceOf(
            SlimFlashMiddleware::class,
            new SlimFlashMiddleware($this->messageProvider, 'flash')
        );
    }

    public function test_attributename_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        new SlimFlashMiddleware($this->messageProvider, '');
    }

    public function test_process_instance_of()
    {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();

        $middleware = new SlimFlashMiddleware($this->messageProvider, 'flash');
        $this->assertInstanceOf(
            ResponseInterface::class,
            $middleware->process($request, new RequestHandler())
        );
    }

    public function test_create()
    {
        $this->assertInstanceOf(
            SlimFlashMiddleware::class,
            SlimFlashMiddleware::create($this->messageProvider, 'flash')
        );
    }

    public function test_createfromcontainer()
    {
        $container = new Container();
        $container->set('flash', $this->messageProvider);
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $this->assertInstanceOf(
            SlimFlashMiddleware::class,
            SlimFlashMiddleware::createFromContainer($app, 'flash')
        );
    }

    public function test_createfromcontainer_null_container()
    {
        $app = AppFactory::create();
        $this->expectException(RuntimeException::class);
        SlimFlashMiddleware::createFromContainer($app, 'flash');
    }

    public function test_createfromcontainer_containerkey_does_not_exist()
    {
        $container = new Container();
        $container->set('flash', $this->messageProvider);
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $this->expectException(RuntimeException::class);
        SlimFlashMiddleware::createFromContainer($app, 'flash_');
    }

    public function test_createfromcontainer_invalid_instance_of()
    {
        $container = new Container();
        $container->set('flash', []);
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $this->expectException(RuntimeException::class);
        SlimFlashMiddleware::createFromContainer($app, 'flash');
    }
}
