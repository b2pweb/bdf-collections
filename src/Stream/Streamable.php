<?php

namespace Bdf\Collection\Stream;

/**
 * Base type for objects which can be streamed
 */
interface Streamable
{
    /**
     * Get the stream from the object
     *
     * @return StreamInterface
     */
    public function stream();
}
