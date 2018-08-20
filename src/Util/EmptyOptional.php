<?php

namespace Bdf\Collection\Util;

use Bdf\Collection\Stream\EmptyStream;

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
    public function filter(callable $predicate)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $transformer)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(callable $consumer)
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
    public function orThrows($exception = \RuntimeException::class)
    {
        if (is_string($exception)) {
            $exception = new $exception;
        }

        throw $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function present()
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
    public function __call($name, array $arguments)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function stream()
    {
        return EmptyStream::instance();
    }

    /**
     * Get the EmptyOptional instance
     *
     * @return EmptyOptional
     */
    public static function instance()
    {
        if (self::$instance) {
            return self::$instance;
        }

        return self::$instance = new EmptyOptional();
    }
}
