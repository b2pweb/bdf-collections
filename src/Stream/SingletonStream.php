<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\AccumulatorInterface;
use Bdf\Collection\Stream\Collector\CollectorInterface;
use Bdf\Collection\Util\Optional;

/**
 * Wrap single value into a stream
 *
 * Unlike other streams, for optimisation reasons, the transformation methods will be called directly, before the terminal call
 */
final class SingletonStream implements StreamInterface
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var mixed
     */
    private $key;

    /**
     * @var bool
     */
    private $closed = false;


    /**
     * SingletonStream constructor.
     *
     * @param mixed $value
     * @param mixed $key
     */
    public function __construct($value, $key = 0)
    {
        $this->value = $value;
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $transformer)
    {
        return new SingletonStream($transformer($this->value, $this->key), $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $predicate)
    {
        return $predicate($this->value, $this->key)
            ? $this
            : EmptyStream::instance()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function distinct(callable $hashFunction = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function sort(callable $comparator = null, $preserveKeys = false)
    {
        return $preserveKeys || $this->key === 0 ? $this : new self($this->value);
    }

    /**
     * {@inheritdoc}
     */
    public function concat(StreamInterface $stream, $preserveKeys = true)
    {
        return new ConcatStream([$this, $stream], $preserveKeys);
    }

    /**
     * {@inheritdoc}
     */
    public function forEach(callable $consumer)
    {
        $consumer($this->value, $this->key);
        $this->closed = true;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($preserveKeys = true)
    {
        return $preserveKeys ? [$this->key => $this->value] : [$this->value];
    }

    /**
     * {@inheritdoc}
     */
    public function first()
    {
        return Optional::nullable($this->value);
    }

    /**
     * {@inheritdoc}
     */
    public function reduce(callable $accumulator, $initial = null)
    {
        if ($initial === null && $accumulator instanceof AccumulatorInterface) {
            $initial = $accumulator->initial();
        }

        return $accumulator($initial, $this->value);
    }

    /**
     * {@inheritdoc}
     */
    public function collect(CollectorInterface $collector)
    {
        $collector->aggregate($this->value, $this->key);

        return $collector->finalize();
    }

    /**
     * {@inheritdoc}
     */
    public function matchAll(callable $predicate)
    {
        return $predicate($this->value, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function matchOne(callable $predicate)
    {
        return $predicate($this->value, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->closed = true;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return !$this->closed;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->closed = false;
    }
}
