<?php

namespace Bdf\Collection\Stream\Accumulator;

use Bdf\Collection\Stream\StreamInterface;

/**
 * Base accumulators for perform standard reduce operations on streams
 *
 * @see AccumulatorInterface
 * @see StreamInterface::reduce()
 */
final class Accumulators
{
    /** @var AccumulatorInterface|null */
    private static $sum;
    /** @var AccumulatorInterface|null */
    private static $multiply;
    /** @var AccumulatorInterface|null */
    private static $min;
    /** @var AccumulatorInterface|null */
    private static $max;

    /** Deny instantiation */
    private function __construct() { }

    /**
     * Perform a sum on each elements of the stream
     *
     * <code>
     * $stream = new ArrayStream([1, 2, 3]);
     * $stream->reduce(Accumulators::sum()); // 6
     * </code>
     *
     * @template V as numeric
     *
     * @return AccumulatorInterface<V, V>
     */
    public static function sum()
    {
        if (self::$sum) {
            return self::$sum;
        }

        return self::$sum = new class implements AccumulatorInterface {
            public function __invoke($carry, $item) { return $carry + $item; }
            public function initial() { return 0; }
        };
    }

    /**
     * Perform a multiplication on each elements of the stream
     *
     * <code>
     * $stream = new ArrayStream([2, 5, 4]);
     * $stream->reduce(Accumulators::multiply()); // 80
     * </code>
     *
     * @template V as numeric
     *
     * @return AccumulatorInterface<V, V>
     */
    public static function multiply()
    {
        if (self::$multiply) {
            return self::$multiply;
        }

        return self::$multiply = new class implements AccumulatorInterface {
            public function __invoke($carry, $item) { return $carry * $item; }
            public function initial() { return 1; }
        };
    }

    /**
     * Get the lowest element of the stream
     *
     * <code>
     * $stream = new ArrayStream([8, 5, 4]);
     * $stream->reduce(Accumulators::min()); // 4
     * </code>
     *
     * @template V
     *
     * @return AccumulatorInterface<V, V>
     */
    public static function min()
    {
        if (self::$min) {
            return self::$min;
        }

        return self::$min = new class implements AccumulatorInterface {
            public function __invoke($carry, $item) { return $item < $carry ? $item : $carry; }
            public function initial() { return PHP_INT_MAX; }
        };
    }

    /**
     * Get the highest element of the stream
     *
     * <code>
     * $stream = new ArrayStream([4, 8, 2]);
     * $stream->reduce(Accumulators::max()); // 8
     * </code>
     *
     * @template V as numeric
     *
     * @return AccumulatorInterface<V, V>
     */
    public static function max()
    {
        if (self::$max) {
            return self::$max;
        }

        return self::$max = new class implements AccumulatorInterface {
            public function __invoke($carry, $item) { return $item > $carry ? $item : $carry; }
            public function initial() { return PHP_INT_MIN; }
        };
    }
}
