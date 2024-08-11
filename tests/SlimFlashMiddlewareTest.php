<?php

namespace Tests;

use DI\Container;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Slim\App;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Response;
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

        $requestHandlerStub = $this->createStub(App::class);
        $requestHandlerStub->method('handle')->willReturn(new Response());

        $middleware = new SlimFlashMiddleware($this->messageProvider, 'flash');
        $this->assertInstanceOf(
            ResponseInterface::class,
            $middleware->process($request, $requestHandlerStub)
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
        $app = new App(new ResponseFactory, $container);
        $this->assertInstanceOf(
            SlimFlashMiddleware::class,
            SlimFlashMiddleware::createFromContainer($app, 'flash')
        );
    }

    public function test_createfromcontainer_null_container()
    {
        $app = new App(new ResponseFactory);
        $this->expectException(RuntimeException::class);
        SlimFlashMiddleware::createFromContainer($app, 'flash');
    }

    public function test_createfromcontainer_containerkey_does_not_exist()
    {
        $container = new Container();
        $container->set('flash', $this->messageProvider);
        $app = new App(new ResponseFactory, $container);
        $this->expectException(RuntimeException::class);
        SlimFlashMiddleware::createFromContainer($app, 'flash_');
    }

    public function test_createfromcontainer_invalid_instance_of()
    {
        $container = new Container();
        $container->set('flash', []);
        $app = new App(new ResponseFactory, $container);
        $this->expectException(RuntimeException::class);
        SlimFlashMiddleware::createFromContainer($app, 'flash');
    }
}
