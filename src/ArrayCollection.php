<?php

namespace Bdf\Collection;

use ArrayIterator;
use Bdf\Collection\Stream\ArrayStream;
use Bdf\Collection\Stream\MutableArrayStream;
use Bdf\Collection\Stream\StreamInterface;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_search;
use function array_values;
use function count;
use function in_array;
use function is_array;
use function iterator_to_array;

/**
 * Collection implementation using native PHP arrays
 *
 * @template T
 * @template K of array-key
 * @implements TableInterface<K, T>
 */
class ArrayCollection implements TableInterface
{
    /**
     * @var array<K, T>
     */
    private $data;


    /**
     * ArrayCollection constructor.
     *
     * @param array<K, T> $data Initial data
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
        return new ArrayIterator($this->data);
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
     *
     * @param K|null $offset
     * @param T $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            /** @psalm-suppress InvalidPropertyAssignmentValue */
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
    public function count(): int
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
     *
     * @psalm-suppress InvalidPropertyAssignmentValue
     */
    public function add($element): bool
    {
        $this->data[] = $element;

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param iterable<mixed, T>|ArrayCollection<T, K> $elements
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
