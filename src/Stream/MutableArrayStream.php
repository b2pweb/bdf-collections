<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\AccumulatorInterface;
use Bdf\Collection\Stream\Collector\CollectorInterface;
use Bdf\Collection\Util\Optional;

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
 */
class MutableArrayStream implements \Iterator, StreamInterface
{
    /**
     * @var array
     */
    private $data;


    /**
     * MutableArrayStream constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $transformer)
    {
        $newData = [];

        foreach ($this->data as $k => $v) {
            $newData[$k] = $transformer($v, $k);
        }

        $this->data = $newData;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $predicate)
    {
        $this->data = array_filter($this->data, $predicate, ARRAY_FILTER_USE_BOTH);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function distinct(callable $hashFunction = null)
    {
        $this->data = array_unique($this->data, SORT_REGULAR);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function sort(callable $comparator = null, $preserveKeys = false)
    {
        if ($comparator) {
            if ($preserveKeys) {
                uasort($this->data, $comparator);
            } else {
                usort($this->data, $comparator);
            }
        } elseif ($preserveKeys) {
            asort($this->data);
        } else {
            sort($this->data);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function concat(StreamInterface $stream, $preserveKeys = true)
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
     */
    public function forEach(callable $consumer)
    {
        foreach ($this->data as $k => $v) {
            $consumer($v, $k);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($preserveKeys = true)
    {
        return $preserveKeys ? $this->data : array_values($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function first()
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
    public function matchAll(callable $predicate)
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
    public function matchOne(callable $predicate)
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
    public function current()
    {
        return current($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return key($this->data) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->data);
    }
}
