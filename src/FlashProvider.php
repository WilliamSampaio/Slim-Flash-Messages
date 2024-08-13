<?php declare(strict_types=1);

namespace SlimFlashMessages;

use Psr\Http\Message\ServerRequestInterface;
use ArrayAccess;
use InvalidArgumentException;
use RuntimeException;

class FlashProvider implements FlashProviderInterface
{
    /**
     * @var null|array|ArrayAccess
     */
    protected $storage;

    protected string $key = '__flash';

    /**
     * Create new Flash messages service provider.
     *
     * @param null|array|ArrayAccess $storage
     * @throws RuntimeException If the session cannot be found.
     * @throws InvalidArgumentException If the store is not array-like.
     */
    public function __construct(&$storage = null, $storageKey = null)
    {
        if (is_string($storageKey) && !empty($storageKey)) {
            $this->key = $storageKey;
        }

        if (is_array($storage) || $storage instanceof ArrayAccess) {
            $this->storage = &$storage;
        } elseif (is_null($storage)) {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                throw new RuntimeException('Session not found.');
            }
            $this->storage = &$_SESSION;
        } else {
            throw new InvalidArgumentException('Storage must be an array or implement \ArrayAccess.');
        }

        // Only if the storage key is not defined will this preserve
        // any other previously set data
        if (!array_key_exists($this->key, $this->storage)) {
            $this->storage[$this->key] = [];
        }
    }

    public function add(string $key, $data)
    {
        if (is_null($key) || empty($key) || !is_string($key)) {
            throw new InvalidArgumentException('Invalid key.');
        }

        if (empty($data)) {
            throw new InvalidArgumentException('Invalid data.');
        }

        // Create Array for this key
        if (!isset($this->storage[$this->key][$key])) {
            $this->storage[$this->key][$key] = [];
        }

        // Push onto the array
        $this->storage[$this->key][$key][] = $data;
    }

    public function get(?string $key = null)
    {
        if ($key === null) {
            return $this->storage[$this->key];
        }

        if (!isset($this->storage[$this->key][$key])) {
            return null;
        }

        return $this->storage[$this->key][$key];
    }

    /**
     * Retrieves all messages or data from storage.
     *
     * @return array All messages or data stored.
     */
    public function getAll()
    {
        return $this->get();
    }

    public function get_first(string $key, bool $remove = false, ?bool $default = null)
    {
        if (
            array_key_exists($key, $this->storage[$this->key]) &&
            is_array($this->storage[$this->key][$key]) &&
            count($this->storage[$this->key][$key]) > 0
        ) {
            if ($remove) {
                return array_shift($this->storage[$this->key][$key]);
            }
            return $this->storage[$this->key][$key][0];
        }
        return $default;
    }

    public function get_last(string $key, bool $remove = false, ?bool $default = null)
    {
        if (
            array_key_exists($key, $this->storage[$this->key]) &&
            is_array($this->storage[$this->key][$key]) &&
            count($this->storage[$this->key][$key]) > 0
        ) {
            if ($remove) {
                return array_pop($this->storage[$this->key][$key]);
            }
            $last_key = array_key_last($this->storage[$this->key][$key]);
            return $this->storage[$this->key][$key][$last_key];
        }
        return $default;
    }

    public function has(string $key)
    {
        return array_key_exists($key, $this->getAll());
    }

    public function clear(?string $key = null)
    {
        if ($key === null) {
            if (isset($this->storage[$this->key])) {
                $this->storage[$this->key] = [];
            }
        } else {
            if (isset($this->storage[$this->key][$key])) {
                unset($this->storage[$this->key][$key]);
            }
        }
    }

    /**
     * Clear all messages or data
     *
     * @return void
     */
    public function clearAll()
    {
        $this->clear();
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return self
     */
    public static function fromRequest(
        ServerRequestInterface $request
    ): self {
        foreach ($request->getAttributes() as $attr) {
            if ($attr instanceof self) {
                return $attr;
            }
        }

        throw new RuntimeException(
            'FlashProvider could not be found in the server request attributes.'
        );
    }
}
