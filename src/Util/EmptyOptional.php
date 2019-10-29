<?php

namespace Bdf\Collection\Util;

use Bdf\Collection\Stream\EmptyStream;
use Bdf\Collection\Stream\StreamInterface;
use RuntimeException;

/**
 * Optional without value
 *
 * @internal
 */
final class EmptyOptional implements OptionalInterface
{
    /**
     * @var EmptyOptional
     */
    private static $instance;

    private function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $predicate): OptionalInterface
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $transformer): OptionalInterface
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(callable $consumer): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function or($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function orSupply(callable $supplier)
    {
        return $supplier();
    }

    /**
     * {@inheritdoc}
     */
    public function orThrows($exception = RuntimeException::class)
    {
        if (is_string($exception)) {
            $exception = new $exception;
        }

        throw $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function present(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function __call(string $name, array $arguments): OptionalInterface
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __get(string $name): OptionalInterface
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __isset(string $name): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function __set(string $name, $value): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function stream(): StreamInterface
    {
        return EmptyStream::instance();
    }

    /**
     * Get the EmptyOptional instance
     *
     * @return EmptyOptional
     */
    public static function instance(): EmptyOptional
    {
        if (self::$instance) {
            return self::$instance;
        }

        return self::$instance = new EmptyOptional();
    }
}
