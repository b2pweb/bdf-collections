<?php

namespace Bdf\Collection\Stream;

/**
 * Wrap an iterator to a stream
 */
class IteratorStream extends \IteratorIterator implements StreamInterface
{
    use StreamTrait;
}
