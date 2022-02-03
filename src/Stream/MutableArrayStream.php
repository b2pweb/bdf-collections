<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\AccumulatorInterface;
use Bdf\Collection\Stream\Collector\CollectorInterface;
use Bdf\Collection\Util\Optional;
use Bdf\Collection\Util\OptionalInterface;
use Iterator;
use function array_filter;
use function array_merge;
use function array_reduce;
use function array_replace;
use function array_slice;
use function array_values;
use function asort;
use function current;
use function key;
use function reset;
use function sort;
use function uasort;
use function usort;

/**
 * Stream for array using native PHP array methods
 * The inner array will be modified by transformation calls
 * So unlike other streams, the transformation methods will be called directly, and returns this
 *
 * This implementation will reduce the overhead on small arrays, but remove the laziness of streams.
 * For big arrays, normal streams are advised
 *
 * Some methods have a different behavior :
 * - distinct() : The hash functor or custom class hash are not used for comparison
 * - first()    : Not optimized in sort() context (all the array will be sorted, instead of find the min value)
 * - mapKey()   : May failed if the function return an invalid key
 *
 * @template T
 * @template K as array-key
 *
 * @implements StreamInterface<T, K>
 * @implements Iterator<K, T>
 */
final class MutableArrayStream implements Iterator, StreamInterface
{
    /**
     * @var array<K, T>
     */
    private $data;


    /**
     * MutableArrayStream constructor.
     *
     * @param array<K, T> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-param callable(T,K=):R $transformer
     * @psalm-return MutableArrayStream<R, K>
     *
     * @template R
     */
    public function map(callable $transformer): StreamInterface
    {
        $newData = [];

        foreach ($this->data as $k => $v) {
            $newData[$k] = $transformer($v, $k);
        }

        /** @var MutableArrayStream<R, K> $this */
        $this->data = $newData;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-param callable(T,K):R $function
     * @psalm-return MutableArrayStream<T, R>
     *
     * @template R as array-key
     */
    public function mapKey(callable $function): StreamInterface
    {
        $newData = [];

        foreach ($this->data as $k => $v) {
            $newData[$function($v, $k)] = $v;
        }

        /** @var MutableArrayStream<T, R> $this */
        $this->data = $newData;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $predicate): StreamInterface
    {
        $this->data = array_filter($this->data, $predicate, ARRAY_FILTER_USE_BOTH);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function distinct(callable $hashFunction = null): StreamInterface
    {
        $this->data = array_unique($this->data, SORT_REGULAR);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function sort(callable $comparator = null, bool $preserveKeys = false): StreamInterface
    {
        if ($comparator) {
            if ($preserveKeys) {
                uasort($this->data, $comparator);
            } else {
                /** @var MutableArrayStream<T, int> $this */
                usort($this->data, $comparator);
            }
        } elseif ($preserveKeys) {
            asort($this->data);
        } else {
            /** @var MutableArrayStream<T, int> $this */
            sort($this->data);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function concat(StreamInterface $stream, bool $preserveKeys = true): StreamInterface
    {
        if ($stream instanceof MutableArrayStream) {
            $this->data = $preserveKeys
                ? array_replace($this->data, $stream->data)
                : array_merge(array_values($this->data), array_values($stream->data))
            ;

            return $this;
        }

        return new ConcatStream([$this, $stream], $preserveKeys);
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress InvalidArgument
     */
    public function flatMap(callable $transformer, bool $preserveKeys = false): StreamInterface
    {
        return new FlatMapStream($this, $transformer, $preserveKeys);
    }

    /**
     * {@inheritdoc}
     */
    public function skip(int $count): StreamInterface
    {
        $this->data = array_slice($this->data, $count, null, true);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function limit(int $count, int $offset = 0): StreamInterface
    {
        $this->data = array_slice($this->data, $offset, $count, true);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function forEach(callable $consumer): void
    {
        foreach ($this->data as $k => $v) {
            $consumer($v, $k);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @template PK as bool
     * @psalm-param PK $preserveKeys
     * @psalm-return (PK is true ? array<K, T> : list<T>)
     */
    public function toArray(bool $preserveKeys = true): array
    {
        return $preserveKeys ? $this->data : array_values($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function first(): OptionalInterface
    {
        if (empty($this->data)) {
            return Optional::empty();
        }

        reset($this->data);

        return Optional::nullable(current($this->data));
    }

    /**
     * {@inheritdoc}
     */
    public function reduce(callable $accumulator, $initial = null)
    {
        return array_reduce(
            $this->data,
            $accumulator,
            $initial === null && $accumulator instanceof AccumulatorInterface ? $accumulator->initial() : $initial
        );
    }

    /**
     * {@inheritdoc}
     */
    public function collect(CollectorInterface $collector)
    {
        foreach ($this->data as $key => $item) {
            $collector->aggregate($item, $key);
        }

        return $collector->finalize();
    }

    /**
     * {@inheritdoc}
     */
    public function matchAll(callable $predicate): bool
    {
        foreach ($this->data as $key => $item) {
            if (!$predicate($item, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function matchOne(callable $predicate): bool
    {
        foreach ($this->data as $key => $item) {
            if ($predicate($item, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return key($this->data) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        reset($this->data);
    }
}
