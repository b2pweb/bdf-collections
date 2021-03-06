<?php

namespace Bdf\Collection\Stream;

use Traversable;
use function is_array;

/**
 * Utility class for handle streams
 */
final class Streams
{
    /**
     * Wrap a value into a stream
     * The best stream implementation is used according to the value type :
     *
     * - If the value is a Streamable object, return the related stream
     * - If the value is null or an empty array, return an EmptyStream
     * - If the value is an array, return an ArrayStream
     * - If the value is Traversable, return an IteratorStream
     * - In other cases, return a SingletonStream
     *
     * @template V
     *
     * @param V|V[]|Traversable<mixed, V> $value The value to wrap
     *
     * @return StreamInterface<V, mixed>
     */
    public static function wrap($value): StreamInterface
    {
        if ($value instanceof StreamInterface) {
            return $value;
        }

        if ($value instanceof Streamable) {
            return $value->stream();
        }

        if ($value === null || $value === []) {
            return EmptyStream::instance();
        }

        if (is_array($value)) {
            return new ArrayStream($value);
        }

        if ($value instanceof Traversable) {
            return new IteratorStream($value);
        }

        return new SingletonStream($value);
    }
}
