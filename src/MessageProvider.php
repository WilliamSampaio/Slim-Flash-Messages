<?php

declare(strict_types=1);

namespace WilliamSampaio\SlimFlashMessages;

use ArrayAccess;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class MessageProvider
{
    /**
     * Storage
     *
     * @var null|array|ArrayAccess
     */
    protected $storage;

    /**
     * Storage key
     *
     * @var string
     */
    protected $storageKey = 'slim_flash_messages';

    /**
     * Create new Flash messages service provider
     *
     * @param null|array|ArrayAccess $storage
     * @throws RuntimeException if the session cannot be found
     * @throws InvalidArgumentException if the store is not array-like
     */
    public function __construct(&$storage = null, $storageKey = null)
    {
        if (is_string($storageKey) && !empty($storageKey)) {
            $this->storageKey = $storageKey;
        }

        if (is_array($storage) || $storage instanceof ArrayAccess) {
            $this->storage = &$storage;
        } elseif (is_null($storage)) {
            if (!isset($_SESSION)) {
                throw new RuntimeException('Session not found.');
            }
            $this->storage = &$_SESSION;
        } else {
            throw new InvalidArgumentException('Storage must be an array or implement \ArrayAccess.');
        }

        // Only if the storage key is not defined will this preserve any other previously set data
        if (!array_key_exists($this->storageKey, $this->storage)) {
            $this->storage[$this->storageKey] = [];
        }
    }

    /**
     * Add message or data
     *
     * @param string $key The key to store the message or data under
     * @param mixed $message Message or data to be retrieved in the next request
     */
    public function add(string $key, $data)
    {
        if (is_null($key) || empty($key) || !is_string($key)) {
            throw new InvalidArgumentException('Invalid key.');
        }

        if (empty($data)) {
            throw new InvalidArgumentException('Invalid data.');
        }

        // Create Array for this key
        if (!isset($this->storage[$this->storageKey][$key])) {
            $this->storage[$this->storageKey][$key] = [];
        }

        // Push onto the array
        $this->storage[$this->storageKey][$key][] = $data;
    }

    /**
     * Retrieves all messages or data from storage
     *
     * @return array All messages or data stored
     */
    public function getAll()
    {
        return $this->storage[$this->storageKey];
    }

    /**
     * Retrieves all messages or data stored by a key
     *
     * @param string $key The key to get the message or data from
     * @return mixed|null Messages or data stored
     */
    public function get(string $key)
    {
        $messages = $this->getAll();
        return (isset($messages[$key])) ? $messages[$key] : null;
    }

    /**
     * Get the first message or data
     *
     * @param string $key The key to get the message or data from
     * @param bool $remove If true removes the item after picking it up
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The message or data
     */
    public function getFirst(string $key, bool $remove = false, $default = null)
    {
        if (
            array_key_exists($key, $this->storage[$this->storageKey]) &&
            is_array($this->storage[$this->storageKey][$key]) &&
            count($this->storage[$this->storageKey][$key]) > 0
        ) {
            if ($remove) {
                return array_shift($this->storage[$this->storageKey][$key]);
            }
            return $this->storage[$this->storageKey][$key][0];
        }
        return $default;
    }

    /**
     * Get the last message or data
     *
     * @param string $key The key to get the message or data from
     * @param bool $remove If true removes the item after picking it up
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The message or data
     */
    public function getLast(string $key, bool $remove = false, $default = null)
    {
        if (
            array_key_exists($key, $this->storage[$this->storageKey]) &&
            is_array($this->storage[$this->storageKey][$key]) &&
            count($this->storage[$this->storageKey][$key]) > 0
        ) {
            if ($remove) {
                return array_pop($this->storage[$this->storageKey][$key]);
            }
            $last_key = array_key_last($this->storage[$this->storageKey][$key]);
            return $this->storage[$this->storageKey][$key][$last_key];
        }
        return $default;
    }

    /**
     * Has message or data
     *
     * @param string $key The key to get the message or data from
     * @return bool Whether the message or data is set or not
     */
    public function has($key)
    {
        return array_key_exists($key, $this->getAll());
    }

    /**
     * Clear all messages or data
     *
     * @return void
     */
    public function clearAll()
    {
        if (isset($this->storage[$this->storageKey])) {
            $this->storage[$this->storageKey] = [];
        }
    }

    /**
     * Clear specific message or data
     *
     * @param string $key The key to clear
     * @return void
     */
    public function clear(string $key)
    {
        if (isset($this->storage[$this->storageKey][$key])) {
            unset($this->storage[$this->storageKey][$key]);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $attributeName
     *
     * @return self
     */
    public static function fromRequest(
        ServerRequestInterface $request,
        string $attributeName = 'slim_flash_messages'
    ): self {
        $messageProvider = $request->getAttribute($attributeName);
        if (!($messageProvider instanceof self)) {
            throw new RuntimeException(
                'MessageProvider could not be found in the server request attributes using the key "' . $attributeName . '".'
            );
        }

        return $messageProvider;
    }
}
