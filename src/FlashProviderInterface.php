<?php declare(strict_types=1);

namespace SlimFlashMessages;

interface FlashProviderInterface
{
    /**
     * Add message or data.
     *
     * @param string $key
     * @param mixed $data
     * @return void
     */
    public function add(string $key, $data);

    /**
     * Retrieves messages or data stored.
     *
     * @param string|null $key The key to get the message or data from, if not set will be return all data stored.
     * @return mixed|null Messages or data stored.
     */
    public function get(?string $key = null);

    /**
     * Get the first message or data.
     *
     * @param string $key The key to get the message or data from.
     * @param boolean $remove If true removes the item after picking it up.
     * @param boolean|null $default Default value if key doesn't exist.
     * @return mixed The message or data.
     */
    public function get_first(string $key, bool $remove = false, bool $default = null);

    /**
     * Get the last message or data.
     *
     * @param string $key The key to get the message or data from.
     * @param boolean $remove If true removes the item after picking it up.
     * @param boolean|null $default Default value if key doesn't exist.
     * @return mixed The message or data.
     */
    public function get_last(string $key, bool $remove = false, bool $default = null);

    /**
     * Has message or data.
     *
     * @param string $key The key to get the message or data from.
     * @return bool Whether the message or data is set or not.
     */
    public function has(string $key);

    /**
     * Clear message or data.
     *
     * @param string|null $key The key to clear, if not set will be clear all data stored.
     * @return void
     */
    public function clear(?string $key = null);
}
