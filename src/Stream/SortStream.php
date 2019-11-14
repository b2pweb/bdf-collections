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
 * @internal
 */
final class SortStream implements Iterator, StreamInterface
{
    use StreamTrait;

    /**
     * @var StreamInterface
     */
    private $stream;

    /**
     * @var callable
     */
    private $comparator;

    /**
     * @var bool
     */
    private $preserveKeys;

    /**
     * @var array|null
     */
    private $data = null;


    /**
     * SortStream constructor.
     *
     * @param StreamInterface $stream
     * @param callable $comparator
     * @param bool $preserveKeys
     */
    public function __construct(StreamInterface $stream, callable $comparator = null, bool $preserveKeys = true)
    {
        $this->stream = $stream;
        $this->comparator = $comparator;
        $this->preserveKeys = $preserveKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(bool $preserveKeys = true): array
    {
        if ($this->data === null) {
            $this->buildData($preserveKeys && $this->preserveKeys);
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
    public function next()
    {
        if ($this->data === null) {
            $this->buildData();
        }

        next($this->data);
    }

    /**
     * {@inheritdoc}
     */
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
    public function valid()
    {
        if ($this->data === null) {
            $this->buildData();
        }

        return key($this->data) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        if ($this->data === null) {
            $this->buildData();
        }

        reset($this->data);
    }

    /**
     * Build the inner sorted data array
     *
     * @param bool $preserveKeys
     */
    private function buildData(bool $preserveKeys = true): void
    {
        $this->data = $this->stream->toArray($preserveKeys);

        if ($this->comparator) {
            if ($preserveKeys) {
                uasort($this->data, $this->comparator);
            } else {
                usort($this->data, $this->comparator);
            }
        } elseif($preserveKeys) {
            asort($this->data);
        } else {
            sort($this->data);
        }
    }
}
