<?php declare(strict_types=1);

namespace SlimFlashMessages;

use Slim\App;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use RuntimeException;

class FlashTwigExtension extends AbstractExtension
{
    protected FlashProviderInterface $flash;

    public function __construct(FlashProviderInterface $flash)
    {
        $this->flash = $flash;
    }

    /**
     * @param App $app
     * @param string $id
     *
     * @return FlashTwigExtension
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

    public function get_messages(?string $key = null, bool $clear = true)
    {
        if (is_null($key)) {
            $data = $this->flash->get();
            if ($clear) {
                $this->flash->clear();
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
        return $this->flash->get_first($key, $remove);
    }

    public function get_last(string $key, $remove = true)
    {
        return $this->flash->get_last($key, $remove);
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('flash', [$this, 'get_messages']),
            new TwigFunction('flash_first', [$this, 'get_first']),
            new TwigFunction('flash_last', [$this, 'get_last']),
            new TwigFunction('flash_has', [$this->flash, 'has']),
            new TwigFunction('flash_clear', [$this->flash, 'clear']),
        ];
    }
}
