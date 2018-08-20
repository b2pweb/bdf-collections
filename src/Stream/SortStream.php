<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Util\Optional;

/**
 * Implementation of StreamInterface::sort() return value
 *
 * @internal
 */
final class SortStream implements \Iterator, StreamInterface
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
    public function __construct(StreamInterface $stream, callable $comparator = null, $preserveKeys = true)
    {
        $this->stream = $stream;
        $this->comparator = $comparator;
        $this->preserveKeys = $preserveKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($preserveKeys = true)
    {
        if ($this->data === null) {
            $preserveKeys &= $this->preserveKeys;

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

        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function forEach(callable $consumer)
    {
        foreach ($this->toArray() as $k => $v) {
            $consumer($v, $k);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function first()
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
        return current($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return key($this->data) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->toArray();
        reset($this->data);
    }
}
