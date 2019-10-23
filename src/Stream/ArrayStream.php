<?php

namespace Bdf\Collection\Stream;

/**
 * Create a stream from a native PHP array
 */
class ArrayStream extends \ArrayIterator implements StreamInterface
{
    use StreamTrait;

    /**
     * ArrayStream constructor.
     *
     * @param array $array The array data
     */
    public function __construct(array $array = [])
    {
        parent::__construct($array);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(bool $preserveKeys = true): array
    {
        $array = $this->getArrayCopy();

        return $preserveKeys
            ? $array
            : array_values($array)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function skip(int $count): StreamInterface
    {
        // out of bound : return an empty stream
        if ($count >= count($this)) {
            return EmptyStream::instance();
        }

        return new LimitStream($this, $count);
    }

    /**
     * {@inheritdoc}
     */
    public function limit(int $count, int $offset = 0): StreamInterface
    {
        // out of bound : return an empty stream
        if ($offset >= count($this) || $count === 0) {
            return EmptyStream::instance();
        }

        return new LimitStream($this, $offset, $count);
    }
}
