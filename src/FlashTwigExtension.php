<?php declare(strict_types=1);

namespace SlimFlashMessages;

use Slim\App;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use RuntimeException;

class FlashTwigExtension extends AbstractExtension
{
    protected FlashProvider $flash;

    public function __construct(FlashProvider $flash)
    {
        $this->flash = $flash;
    }

    /**
     * @param App $app
     * @param string $containerKey
     *
     * @return FlashTwigExtension
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
                "The specified container key does not exist: $containerKey."
            );
        }

        $flash = $container->get($containerKey);

        if (!($flash instanceof FlashProvider)) {
            throw new RuntimeException(
                "FlashProvider instance could not be resolved via container key: $containerKey."
            );
        }

        return new self($flash);
    }

    public function get_messages(?string $key = null, bool $clear = true)
    {
        if (is_null($key)) {
            $data = $this->flash->getAll();
            if ($clear) {
                $this->flash->clearAll();
            }
            return $data;
        }

        $data = $this->flash->get($key);
        if ($data === null)
            return [];

        if ($clear) {
            $this->flash->clear($key);
        }
        return $data;
    }

    public function get_first(string $key, $remove = true)
    {
        return $this->flash->getFirst($key, $remove);
    }

    public function get_last(string $key, $remove = true)
    {
        return $this->flash->getLast($key, $remove);
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('flash', [$this, 'get_messages']),
            new TwigFunction('flash_first', [$this, 'get_first']),
            new TwigFunction('flash_last', [$this, 'get_last']),
            new TwigFunction('flash_has', [$this->flash, 'has']),
            new TwigFunction('flash_clear', [$this->flash, 'clear']),
            new TwigFunction('flash_clear_all', [$this->flash, 'clearAll']),
        ];
    }
}
