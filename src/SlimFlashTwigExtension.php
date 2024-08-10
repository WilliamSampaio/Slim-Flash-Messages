<?php

declare(strict_types=1);

namespace WilliamSampaio\SlimFlashMessages;

use RuntimeException;
use Slim\App;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SlimFlashTwigExtension extends AbstractExtension
{
    protected MessageProvider $messageProvider;

    /**
     * @param App $app
     * @param string $containerKey
     *
     * @return SlimFlashTwigExtension
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

        return new self($messageProvider);
    }

    public function __construct(MessageProvider $messageProvider)
    {
        $this->messageProvider = $messageProvider;
    }

    public function get_messages(?string $key = null, bool $clear = true)
    {
        if (is_null($key)) {
            $data = $this->messageProvider->getAll();
            if ($clear) {
                $this->messageProvider->clearAll();
            }
            return $data;
        }

        $data = $this->messageProvider->get($key);
        if ($data === null) return [];

        if ($clear) {
            $this->messageProvider->clear($key);
        }
        return $data;
    }

    public function get_first(string $key, $remove = true)
    {
        return $this->messageProvider->getFirst($key, $remove);
    }

    public function get_last(string $key, $remove = true)
    {
        return $this->messageProvider->getLast($key, $remove);
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('flash', [$this, 'get_messages']),
            new TwigFunction('flash_first', [$this, 'get_first']),
            new TwigFunction('flash_last', [$this, 'get_last']),
            new TwigFunction('flash_has', [$this->messageProvider, 'has']),
            new TwigFunction('flash_clear', [$this->messageProvider, 'clear']),
            new TwigFunction('flash_clear_all', [$this->messageProvider, 'clearAll']),
        ];
    }
}
