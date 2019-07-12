<?php

namespace Bdf\Collection;

use Bdf\Collection\Stream\ArrayStream;
use Bdf\Collection\Stream\MutableArrayStream;
use Bdf\Collection\Stream\StreamInterface;

/**
 * Collection implementation using native PHP arrays
 */
class ArrayCollection implements TableInterface
{
    /**
     * @var array
     */
    private $data;


    /**
     * ArrayCollection constructor.
     *
     * @param array $data Initial data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($element, bool $strict = false): bool
    {
        return in_array($element, $this->data, $strict) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($element, bool $strict = false): bool
    {
        $key = array_search($element, $this->data, $strict);

        if ($key === false) {
            return false;
        }

        unset($this->data[$key]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * {@inheritdoc}
     */
    public function empty(): bool
    {
        return empty($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function &offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function stream(): StreamInterface
    {
        return new ArrayStream($this->data);
    }

    /**
     * Get a mutable stream
     * All transformation methods will be applied directly, and modify the current stream state, instead of creating a new one
     *
     * /!\ Mutable stream have a slightly different behavior, and cannot be used in all situations
     *
     * @return StreamInterface
     *
     * @see MutableArrayStream
     */
    public function mutableStream(): StreamInterface
    {
        return new MutableArrayStream($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function &get($key)
    {
        if (!isset($this->data[$key])) {
            throw new \OutOfBoundsException('Cannot found element at key '.$key);
        }

        return $this->data[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function add($element): bool
    {
        $this->data[] = $element;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addAll(iterable $elements): bool
    {
        if ($elements instanceof ArrayCollection) {
            $elements = $elements->data;
        } elseif (!is_array($elements)) {
            $elements = iterator_to_array($elements, false);
        }

        $this->data = array_merge($this->data, $elements);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function replace(iterable $elements): bool
    {
        $this->clear();

        if (is_array($elements)) {
            $this->data = $elements;
        } elseif ($elements instanceof ArrayCollection) {
            $this->data = $elements->data;
        } else {
            $this->data = iterator_to_array($elements);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function hasKey($key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function unset($key): bool
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function keys(): array
    {
        return array_keys($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function values(): array
    {
        return array_values($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function forEach(callable $consumer): void
    {
        foreach ($this->data as $key => $value) {
            $consumer($value, $key);
        }
    }
}
