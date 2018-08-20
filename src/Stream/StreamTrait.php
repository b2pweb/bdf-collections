<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\HashSet;
use Bdf\Collection\Stream\Accumulator\AccumulatorInterface;
use Bdf\Collection\Stream\Collector\CollectorInterface;
use Bdf\Collection\Util\Optional;

/**
 * Implementation of base stream methods
 */
trait StreamTrait
{
    /**
     * @see StreamInterface::filter()
     */
    public function filter(callable $predicate)
    {
        return new FilterStream($this, $predicate);
    }

    /**
     * @see StreamInterface::map()
     */
    public function map(callable $transformer)
    {
        return new MapStream($this, $transformer);
    }

    /**
     * @see StreamInterface::distinct()
     */
    public function distinct(callable $hashFunction = null)
    {
        return new DistinctStream($this, new HashSet($hashFunction));
    }

    /**
     * @see StreamInterface::sort()
     */
    public function sort(callable $comparator = null, $preserveKeys = false)
    {
        return new SortStream($this, $comparator, $preserveKeys);
    }

    /**
     * @see StreamInterface::forEach()
     */
    public function forEach(callable $consumer)
    {
        foreach ($this as $key => $value) {
            $consumer($value, $key);
        }
    }

    /**
     * @see StreamInterface::toArray()
     */
    public function toArray($preserveKeys = true)
    {
        return iterator_to_array($this, $preserveKeys);
    }

    /**
     * @see StreamInterface::first()
     */
    public function first()
    {
        foreach ($this as $value) {
            return Optional::nullable($value);
        }

        return Optional::empty();
    }

    /**
     * @see StreamInterface::reduce()
     */
    public function reduce(callable $accumulator, $initial = null)
    {
        $carry = $initial === null && $accumulator instanceof AccumulatorInterface
            ? $accumulator->initial()
            : $initial
        ;

        foreach ($this as $item) {
            $carry = $accumulator($carry, $item);
        }

        return $carry;
    }

    /**
     * @see StreamInterface::collect()
     */
    public function collect(CollectorInterface $collector)
    {
        foreach ($this as $key => $item) {
            $collector->aggregate($item, $key);
        }

        return $collector->finalize();
    }
}
