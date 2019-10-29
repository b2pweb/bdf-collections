<?php

namespace Bdf\Collection\Stream;

use LimitIterator;
use OutOfBoundsException;

/**
 * Implementation of StreamInterface::limit() and StreamInterface::skip() return value
 *
 * @internal
 */
final class LimitStream extends LimitIterator implements StreamInterface
{
    use StreamTrait;

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        try {
            parent::rewind();
        } catch (OutOfBoundsException $e) {
            // Ignore OutOfBound exception (raised when offset > count)
        }
    }
}
