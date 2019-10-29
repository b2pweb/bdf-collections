<?php

namespace Bdf\Collection\Stream;

use CallbackFilterIterator;

/**
 * Implementation of StreamInterface::filter() return value
 *
 * @internal
 */
final class FilterStream extends CallbackFilterIterator implements StreamInterface
{
    use StreamTrait;
}
