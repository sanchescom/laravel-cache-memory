<?php

namespace Sanchescom\Cache;

use Illuminate\Cache\RetrievesMultipleKeys;
use Illuminate\Contracts\Cache\Store as StoreInterface;
use Illuminate\Support\InteractsWithTime;

/**
 * Class Store.
 */
class MemoryStore implements StoreInterface
{
    use InteractsWithTime, RetrievesMultipleKeys;

    /** @var \Sanchescom\Cache\MemoryBlock */
    protected $memory;

    /**
     * @param \Sanchescom\Cache\MemoryBlock
     */
    public function __construct(MemoryBlock $memoryBlock)
    {
        $this->memory = $memoryBlock;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string|array $key
     *
     * @return string|null
     */
    public function get($key)
    {
        $storage = $this->getStorage();

        if ($storage === false) {
            return null;
        }

        if (!isset($storage[$key])) {
            return null;
        }

        $item = $storage[$key];

        $expiresAt = $item['expiresAt'] ?? 0;

        if ($expiresAt !== 0 && $this->currentTime() > $expiresAt) {
            $this->forget($key);

            return null;
        }

        return $item['value'];
    }

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param string $key
     * @param mixed $value
     * @param int $seconds
     *
     * @return bool
     */
    public function put($key, $value, $seconds)
    {
        $storage = $this->getStorage();

        if ($storage === false) {
            // PHP-8.1 auto-vivication from false is deprecated
            $storage = [];
        }

        $storage[$key] = [
            'value' => $value,
            'expiresAt' => $this->calculateExpiration($seconds),
        ];

        $this->setStorage($storage);

        return true;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        $storage = $this->getStorage();

        if (!$storage || !isset($storage[$key])) {
            $this->forever($key, $value);

            return $storage[$key]['value'];
        }

        $storage[$key]['value'] = ((int)$storage[$key]['value']) + $value;

        $this->setStorage($storage);

        return $storage[$key]['value'];
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return bool
     */
    public function forever($key, $value)
    {
        return $this->put($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function forget($key)
    {
        $storage = $this->getStorage();

        if ($storage === false) {
            // prevents "array_key_exists() argument 2 must be of type array, bool given"
            return false;
        }

        if (array_key_exists($key, $storage)) {
            unset($storage[$key]);

            $this->setStorage($storage);

            return true;
        }

        return false;
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        $this->setStorage([]);

        return true;
    }

    /**
     * Save data in memory storage
     *
     * @param $data
     *
     * @return void
     */
    protected function setStorage($data)
    {
        $this->memory->write($this->serialize($data));
    }

    /**
     * Get data from memory storage
     *
     * @return array
     */
    protected function getStorage()
    {
        return $this->unserialize($this->memory->read());
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return '';
    }

    /**
     * Get the expiration time of the key.
     *
     * @param int $seconds
     *
     * @return int
     */
    protected function calculateExpiration($seconds)
    {
        return $this->toTimestamp($seconds);
    }

    /**
     * Get the UNIX timestamp for the given number of seconds.
     *
     * @param int $seconds
     *
     * @return int
     */
    protected function toTimestamp($seconds)
    {
        return $seconds > 0 ? $this->availableAt($seconds) : 0;
    }

    /**
     * Serialize the value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function serialize($value)
    {
        return is_numeric($value) ? $value : @serialize($value);
    }

    /**
     * Unserialize the value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function unserialize($value)
    {
        return is_numeric($value) ? $value : @unserialize($value);
    }
}
