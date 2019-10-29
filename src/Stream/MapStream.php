<?php

namespace Bdf\Collection\Stream;

use IteratorIterator;

/**
 * Implementation of StreamInterface::filter() return value
 *
 * @internal
 */
final class MapStream extends IteratorIterator implements StreamInterface
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
    public function current()
    {
        return ($this->transformer)(parent::current(), $this->key());
    }
}
