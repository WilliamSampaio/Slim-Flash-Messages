<?php declare(strict_types=1);

namespace SlimFlashMessages\Tests;

use DI\Container;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Response;
use Slim\App;
use SlimFlashMessages\FlashMiddleware;
use SlimFlashMessages\FlashProvider;
use RuntimeException;

#[CoversClass(FlashMiddleware::class)]
#[UsesClass(FlashProvider::class)]
class FlashMiddlewareTest extends TestCase
{
    private array $storage;
    private FlashProvider $flash_provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = [];
        $this->flash_provider = new FlashProvider($this->storage);
    }

    public function test_process_instance_of()
    {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();

        $requestHandlerStub = $this->createStub(App::class);
        $requestHandlerStub->method('handle')->willReturn(new Response());

        $middleware = new FlashMiddleware($this->flash_provider, 'flash');
        $this->assertInstanceOf(
            ResponseInterface::class,
            $middleware->process($request, $requestHandlerStub)
        );
    }

    public function test_create()
    {
        $this->assertInstanceOf(
            FlashMiddleware::class,
            FlashMiddleware::create($this->flash_provider, 'flash')
        );
    }

    public function test_createfromcontainer()
    {
        $container = new Container();
        $container->set('flash', $this->flash_provider);
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
        $container->set('flash', $this->flash_provider);
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
