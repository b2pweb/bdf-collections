<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\AccumulatorInterface;
use Bdf\Collection\Stream\Collector\CollectorInterface;
use Bdf\Collection\Util\Optional;
use Bdf\Collection\Util\OptionalInterface;

/**
 * Wrap single value into a stream
 *
 * Unlike other streams, for optimisation reasons, the transformation methods will be called directly, before the terminal call
 *
 * @template T
 * @template K
 *
 * @implements StreamInterface<T, K>
 */
final class SingletonStream implements StreamInterface
{
    /**
     * @var T
     */
    private $value;

    /**
     * @var K
     */
    private $key;

    /**
     * @var bool
     */
    private $closed = false;


    /**
     * SingletonStream constructor.
     *
     * @param T $value
     * @param K $key
     */
    public function __construct($value, $key = 0)
    {
        $this->value = $value;
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $transformer): StreamInterface
    {
        return new SingletonStream($transformer($this->value, $this->key), $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function mapKey(callable $function): StreamInterface
    {
        return new SingletonStream($this->value, $function($this->value, $this->key));
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $predicate): StreamInterface
    {
        return $predicate($this->value, $this->key)
            ? $this
            : EmptyStream::instance()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function distinct(?callable $hashFunction = null): StreamInterface
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function sort(?callable $comparator = null, bool $preserveKeys = false): StreamInterface
    {
        return $preserveKeys || $this->key === 0 ? $this : new self($this->value);
    }

    /**
     * {@inheritdoc}
     */
    public function concat(StreamInterface $stream, bool $preserveKeys = true): StreamInterface
    {
        return new ConcatStream([$this, $stream], $preserveKeys);
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress InvalidArgument
     */
    public function flatMap(callable $transformer, bool $preserveKeys = false): StreamInterface
    {
        if ($preserveKeys) {
            return Streams::wrap($transformer($this->value, $this->key));
        }

        return new FlatMapStream($this, $transformer, false);
    }

    /**
     * {@inheritdoc}
     */
    public function skip(int $count): StreamInterface
    {
        if ($count > 0) {
            return EmptyStream::instance();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function limit(int $count, int $offset = 0): StreamInterface
    {
        if ($offset > 0 || $count < 1) {
            return EmptyStream::instance();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function forEach(callable $consumer): void
    {
        $consumer($this->value, $this->key);
        $this->closed = true;
    }

    /**
     * {@inheritdoc}
     *
     * @template PK as bool
     * @psalm-param PK $preserveKeys
     * @psalm-return (PK is true ? array<K, T> : array{0:T})
     */
    public function toArray(bool $preserveKeys = true): array
    {
        return $preserveKeys ? [$this->key => $this->value] : [$this->value];
    }

    /**
     * {@inheritdoc}
     */
    public function first(): OptionalInterface
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
    public function matchAll(callable $predicate): bool
    {
        return $predicate($this->value, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function matchOne(callable $predicate): bool
    {
        return $predicate($this->value, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        $this->closed = true;
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return !$this->closed;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->closed = false;
    }
}
