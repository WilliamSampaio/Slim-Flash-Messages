<?php

declare(strict_types=1);

namespace WilliamSampaio\SlimFlashMessages;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SlimFlashTwigExtension extends AbstractExtension
{
    protected MessageProvider $messageProvider;

    public function __construct(MessageProvider $messageProvider)
    {
        $this->messageProvider = $messageProvider;
    }

    public function get_messages($key = null)
    {
        if (is_null($key)) {
            return $this->messageProvider->getAll();
        }

        return $this->messageProvider->get($key) ?? [];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('flash', [$this, 'get_messages']),
            new TwigFunction('flash_first', [$this->messageProvider, 'getFirst']),
            new TwigFunction('flash_last', [$this->messageProvider, 'getLast']),
            new TwigFunction('flash_has', [$this->messageProvider, 'has']),
        ];
    }
}
