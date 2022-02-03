<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\SetInterface;
use FilterIterator;

/**
 * Implementation of StreamInterface::distinct() return value
 *
 * @internal
 */
final class DistinctStream extends FilterIterator implements StreamInterface
{
    use StreamTrait;

    /**
     * @var SetInterface
     */
    private $set;


    /**
     * FilterStream constructor.
     *
     * @param StreamInterface $stream The stream to filter
     * @param SetInterface $set The set used to store elements
     */
    public function __construct(StreamInterface $stream, SetInterface $set)
    {
        parent::__construct($stream);

        $this->set = $set;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(): bool
    {
        return $this->set->add($this->current());
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->set->clear();

        parent::rewind();
    }
}
