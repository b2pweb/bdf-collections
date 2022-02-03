<?php

namespace Bdf\Collection\Stream;

use IteratorIterator;

/**
 * Implementation of StreamInterface::mapKey() return value
 *
 * @internal
 */
final class MapKeyStream extends IteratorIterator implements StreamInterface
{
    use StreamTrait;

    /**
     * @var callable
     */
    private $transformer;


    /**
     * MapStream constructor.
     *
     * @param StreamInterface $stream
     * @param callable $transformer
     */
    public function __construct(StreamInterface $stream, callable $transformer)
    {
        parent::__construct($stream);

        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        $key = parent::key();

        return ($this->transformer)($this->current(), $key);
    }
}
