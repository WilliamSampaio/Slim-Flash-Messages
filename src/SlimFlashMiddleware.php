<?php

declare(strict_types=1);

namespace WilliamSampaio\SlimFlashMessages;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Slim\App;

class SlimFlashMiddleware implements MiddlewareInterface
{
    protected MessageProvider $messageProvider;

    protected string $attributeName;

    /**
     * @param App $app
     * @param string $containerKey
     *
     * @return SlimFlashMiddleware
     */
    public static function createFromContainer(
        App $app,
        string $containerKey = 'slim_flash_messages'
    ): self {
        $container = $app->getContainer();

        if ($container === null) {
            throw new RuntimeException('The app does not have a container.');
        }

        if (!$container->has($containerKey)) {
            throw new RuntimeException(
                "The specified container key does not exist: $containerKey"
            );
        }

        $messageProvider = $container->get($containerKey);

        if (!($messageProvider instanceof MessageProvider)) {
            throw new RuntimeException(
                "MessageProvider instance could not be resolved via container key: $containerKey"
            );
        }

        return new self($messageProvider, $containerKey);
    }

    /**
     * @param MessageProvider $messageProvider
     * @param string $attributeName
     *
     * @return SlimFlashMiddleware
     */
    public static function create(
        MessageProvider $messageProvider,
        string $attributeName = 'slim_flash_messages'
    ): self {
        return new self(
            $messageProvider,
            $attributeName
        );
    }

    /**
     * @param MessageProvider $messageProvider
     * @param string $attributeName
     */
    public function __construct(
        MessageProvider $messageProvider,
        string $attributeName
    ) {
        $this->messageProvider = $messageProvider;

        if (is_string($attributeName) && !empty($attributeName)) {
            $this->attributeName = $attributeName;
        } else {
            throw new InvalidArgumentException("Invalid value for attributeName.");
        }
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
        $request = $request->withAttribute($this->attributeName, $this->messageProvider);
        return $handler->handle($request);
    }
}
