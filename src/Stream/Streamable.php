<?php

namespace Bdf\Collection\Stream;

/**
 * Base type for objects which can be streamed
 *
 * @template T
 * @template K
 */
interface Streamable
{
    /**
     * Get the stream from the object
     *
     * @return StreamInterface<T, K>
     */
    public function stream(): StreamInterface;
}
