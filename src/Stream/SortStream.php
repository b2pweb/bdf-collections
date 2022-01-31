<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Util\Optional;
use Bdf\Collection\Util\OptionalInterface;
use Iterator;
use function asort;
use function current;
use function key;
use function next;
use function reset;
use function sort;
use function uasort;
use function usort;

/**
 * Implementation of StreamInterface::sort() return value
 *
 * @template T
 *
 * @implements StreamInterface<T, array-key>
 * @implements Iterator<array-key, T>
 *
 * @internal
 */
final class SortStream implements Iterator, StreamInterface
{
    use StreamTrait;

    /**
     * @var StreamInterface<T, mixed>
     */
    private $stream;

    /**
     * @var callable(T,T):int|null
     */
    private $comparator;

    /**
     * @var bool
     */
    private $preserveKeys;

    /**
     * @var T[]|null
     */
    private $data = null;


    /**
     * SortStream constructor.
     *
     * @param StreamInterface<T, mixed> $stream
     * @param callable(T,T):int|null $comparator
     * @param bool $preserveKeys
     */
    public function __construct(StreamInterface $stream, ?callable $comparator = null, bool $preserveKeys = true)
    {
        $this->stream = $stream;
        $this->comparator = $comparator;
        $this->preserveKeys = $preserveKeys;
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-assert !null $this->data
     * @psalm-suppress InvalidReturnType
     */
    public function toArray(bool $preserveKeys = true): array
    {
        if ($this->data === null) {
            $this->buildData();
        }

        // Built data keep keys, but toArray() request without keys
        // So call array_values to remove keys
        if (!$preserveKeys && $this->preserveKeys) {
            return array_values($this->data);
        }

        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function forEach(callable $consumer): void
    {
        foreach ($this->toArray() as $k => $v) {
            $consumer($v, $k);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function first(): OptionalInterface
    {
        $empty = true;
        $min = null;

        foreach ($this->stream as $value) {
            if ($empty) {
                $min = $value;
                $empty = false;
            } else {
                if ($this->comparator === null) {
                    if ($value < $min) {
                        $min = $value;
                    }
                } elseif (($this->comparator)($value, $min) < 0) {
                    $min = $value;
                }
            }
        }

        return Optional::nullable($min);
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        if ($this->data === null) {
            $this->buildData();
        }

        return current($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        if ($this->data === null) {
            $this->buildData();
        }

        next($this->data);
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        if ($this->data === null) {
            $this->buildData();
        }

        return key($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        if ($this->data === null) {
            $this->buildData();
        }

        return key($this->data) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        if ($this->data === null) {
            $this->buildData();
        }

        reset($this->data);
    }

    /**
     * Build the inner sorted data array
     *
     * @psalm-assert !null $this->data
     */
    private function buildData(): void
    {
        $data = $this->stream->toArray($this->preserveKeys);

        if ($this->comparator) {
            if ($this->preserveKeys) {
                uasort($data, $this->comparator);
            } else {
                usort($data, $this->comparator);
            }
        } elseif($this->preserveKeys) {
            asort($data);
        } else {
            sort($data);
        }

        $this->data = $data;
    }
}
