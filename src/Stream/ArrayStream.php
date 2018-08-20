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
    public function toArray($preserveKeys = true)
    {
        $array = $this->getArrayCopy();

        return $preserveKeys
            ? $array
            : array_values($array)
        ;
    }
}
