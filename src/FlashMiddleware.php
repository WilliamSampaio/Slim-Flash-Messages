<?php declare(strict_types=1);

namespace SlimFlashMessages;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use RuntimeException;

class FlashMiddleware implements MiddlewareInterface
{
    protected FlashProviderInterface $flash;

    protected string $attributeName = 'flash_messages';

    public function __construct(
        FlashProviderInterface $flash,
    ) {
        $this->flash = $flash;
    }

    /**
     * @param App $app
     * @param string $id
     *
     * @return FlashMiddleware
     */
    public static function createFromContainer(
        App $app,
        string $id = FlashProviderInterface::class
    ): self {
        $container = $app->getContainer();

        if ($container === null) {
            throw new RuntimeException('The app does not have a container.');
        }

        if (!$container->has($id)) {
            throw new RuntimeException(
                "The specified container id does not exist: $id."
            );
        }

        $flash = $container->get($id);

        if (!($flash instanceof FlashProvider)) {
            throw new RuntimeException(
                "FlashProvider instance could not be resolved via container id: $id."
            );
        }

        return new self($flash);
    }

    /**
     * @param FlashProvider $flash
     *
     * @return FlashMiddleware
     */
    public static function create(
        FlashProviderInterface $flash,
    ): self {
        return new self(
            $flash
        );
    }

    /**
     * Process an incoming server request.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $request = $request->withAttribute($this->attributeName, $this->flash);
        return $handler->handle($request);
    }
}
