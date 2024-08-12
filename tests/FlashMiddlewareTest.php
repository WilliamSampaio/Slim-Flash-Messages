<?php

namespace Tests;

use DI\Container;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Slim\App;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Response;
use WilliamSampaio\SlimFlashMessages\FlashProvider;
use WilliamSampaio\SlimFlashMessages\FlashMiddleware;

#[CoversClass(FlashMiddleware::class)]
#[UsesClass(FlashProvider::class)]
class FlashMiddlewareTest extends TestCase
{
    private array $storage;
    private FlashProvider $messageProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = [];
        $this->messageProvider = new FlashProvider($this->storage);
    }

    public function test_process_instance_of()
    {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();

        $requestHandlerStub = $this->createStub(App::class);
        $requestHandlerStub->method('handle')->willReturn(new Response());

        $middleware = new FlashMiddleware($this->messageProvider, 'flash');
        $this->assertInstanceOf(
            ResponseInterface::class,
            $middleware->process($request, $requestHandlerStub)
        );
    }

    public function test_create()
    {
        $this->assertInstanceOf(
            FlashMiddleware::class,
            FlashMiddleware::create($this->messageProvider, 'flash')
        );
    }

    public function test_createfromcontainer()
    {
        $container = new Container();
        $container->set('flash', $this->messageProvider);
        $app = new App(new ResponseFactory, $container);
        $this->assertInstanceOf(
            FlashMiddleware::class,
            FlashMiddleware::createFromContainer($app, 'flash')
        );
    }

    public function test_createfromcontainer_null_container()
    {
        $app = new App(new ResponseFactory);
        $this->expectException(RuntimeException::class);
        FlashMiddleware::createFromContainer($app, 'flash');
    }

    public function test_createfromcontainer_containerkey_does_not_exist()
    {
        $container = new Container();
        $container->set('flash', $this->messageProvider);
        $app = new App(new ResponseFactory, $container);
        $this->expectException(RuntimeException::class);
        FlashMiddleware::createFromContainer($app, 'flash_');
    }

    public function test_createfromcontainer_invalid_instance_of()
    {
        $container = new Container();
        $container->set('flash', []);
        $app = new App(new ResponseFactory, $container);
        $this->expectException(RuntimeException::class);
        FlashMiddleware::createFromContainer($app, 'flash');
    }
}
