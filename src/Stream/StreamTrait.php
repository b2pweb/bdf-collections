<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\HashSet;
use Bdf\Collection\Stream\Accumulator\AccumulatorInterface;
use Bdf\Collection\Stream\Collector\CollectorInterface;
use Bdf\Collection\Util\Optional;
use Bdf\Collection\Util\OptionalInterface;
use function iterator_to_array;

/**
 * Implementation of base stream methods
 */
trait StreamTrait
{
    /**
     * @see StreamInterface::filter()
     */
    public function filter(callable $predicate): StreamInterface
    {
        return new FilterStream($this, $predicate);
    }

    /**
     * @see StreamInterface::map()
     */
    public function map(callable $transformer): StreamInterface
    {
        return new MapStream($this, $transformer);
    }

    /**
     * @see StreamInterface::mapKey()
     */
    public function mapKey(callable $transformer): StreamInterface
    {
        return new MapKeyStream($this, $transformer);
    }

    /**
     * @see StreamInterface::distinct()
     */
    public function distinct(callable $hashFunction = null): StreamInterface
    {
        return new DistinctStream($this, new HashSet($hashFunction));
    }

    /**
     * @see StreamInterface::sort()
     */
    public function sort(callable $comparator = null, bool $preserveKeys = false): StreamInterface
    {
        return new SortStream($this, $comparator, $preserveKeys);
    }

    /**
     * @see StreamInterface::concat()
     */
    public function concat(StreamInterface $stream, bool $preserveKeys = true): StreamInterface
    {
        return new ConcatStream([$this, $stream], $preserveKeys);
    }

    /**
     * @see StreamInterface::flatMap()
     */
    public function flatMap(callable $transformer, bool $preserveKeys = false): StreamInterface
    {
        return new FlatMapStream($this, $transformer, $preserveKeys);
    }

    /**
     * @see StreamInterface::skip()
     */
    public function skip(int $count): StreamInterface
    {
        return new LimitStream($this, $count);
    }

    /**
     * @see StreamInterface::limit()
     */
    public function limit(int $count, int $offset = 0): StreamInterface
    {
        return new LimitStream($this, $offset, $count);
    }

    /**
     * @see StreamInterface::forEach()
     */
    public function forEach(callable $consumer): void
    {
        foreach ($this as $key => $value) {
            $consumer($value, $key);
        }
    }

    /**
     * @see StreamInterface::toArray()
     */
    public function toArray(bool $preserveKeys = true): array
    {
        return iterator_to_array($this, $preserveKeys);
    }

    /**
     * @see StreamInterface::first()
     */
    public function first(): OptionalInterface
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

    /**
     * @see StreamInterface::matchAll()
     */
    public function matchAll(callable $predicate): bool
    {
        foreach ($this as $key => $item) {
            if (!$predicate($item, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @see StreamInterface::matchOne()
     */
    public function matchOne(callable $predicate): bool
    {
        foreach ($this as $key => $item) {
            if ($predicate($item, $key)) {
                return true;
            }
        }

        return false;
    }
}
