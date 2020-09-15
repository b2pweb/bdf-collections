<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\AccumulatorInterface;
use Bdf\Collection\Stream\Collector\CollectorInterface;
use Bdf\Collection\Util\Optional;
use Bdf\Collection\Util\OptionalInterface;
use EmptyIterator;

/**
 * Null object for streams
 */
final class EmptyStream extends EmptyIterator implements StreamInterface
{
    /**
     * @var EmptyStream|null
     */
    private static $instance;


    /**
     * {@inheritdoc}
     */
    public function map(callable $transformer): StreamInterface
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function mapKey(callable $function): StreamInterface
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $predicate): StreamInterface
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function distinct(callable $hashFunction = null): StreamInterface
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function sort(callable $comparator = null, bool $preserveKeys = false): StreamInterface
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function concat(StreamInterface $stream, bool $preserveKeys = true): StreamInterface
    {
        return $preserveKeys ? $stream : new ConcatStream([$stream], false);
    }

    /**
     * {@inheritdoc}
     */
    public function flatMap(callable $transformer, bool $preserveKeys = false): StreamInterface
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function skip(int $count): StreamInterface
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function limit(int $count, int $offset = 0): StreamInterface
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function forEach(callable $consumer): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(bool $preserveKeys = true): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function first(): OptionalInterface
    {
        return Optional::empty();
    }

    /**
     * {@inheritdoc}
     */
    public function reduce(callable $accumulator, $initial = null)
    {
        return $initial === null && $accumulator instanceof AccumulatorInterface
            ? $accumulator->initial()
            : $initial
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(CollectorInterface $collector)
    {
        return $collector->finalize();
    }

    /**
     * {@inheritdoc}
     */
    public function matchAll(callable $predicate): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function matchOne(callable $predicate): bool
    {
        return false;
    }

    /**
     * Get the Empty stream instance
     *
     * @return EmptyStream
     */
    public static function instance(): EmptyStream
    {
        if (self::$instance) {
            return self::$instance;
        }

        return self::$instance = new EmptyStream();
    }
}
