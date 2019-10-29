<?php

namespace Bdf\Collection\Stream;

/**
 * Wrap an iterator to a stream
 */
final class IteratorStream extends \IteratorIterator implements StreamInterface
{
    use StreamTrait;
}
