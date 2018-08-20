<?php

namespace Bdf\Collection\Stream\Accumulator;

use Bdf\Collection\Stream\StreamInterface;

/**
 * Base type for reduce operation accumulator
 *
 * <code>
 * class MultiplyAccumulate implements AccumulatorInterface
 * {
 *     private $factor;
 *
 *     public function __construct($factor) { $this->factor = $factor; }
 *
 *     public function __invoke($carry, $item) { return $this->factor * $carry + $item; }
 *
 *     public function initial() { return 0; }
 * }
 * </code>
 *
 * @see StreamInterface::reduce()
 */
interface AccumulatorInterface
{
    /**
     * Accumulate $item into $carry and return the new value
     * The type of initial value, carry and return value must have the same type
     *
     * @param mixed $carry The value of the previous call, or the initial() value
     * @param mixed $item The item to accumulate
     *
     * @return mixed The accumulated value
     */
    public function __invoke($carry, $item);

    /**
     * The initial accumulator value
     * If the input is empty, this value will be the result of the reduce operation
     *
     * @return mixed
     */
    public function initial();
}
