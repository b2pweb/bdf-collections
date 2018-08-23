<?php

namespace Bdf\Collection\Util;

use Bdf\Collection\Stream\SingletonStream;

/**
 * Handle null values, and create simple null objects
 *
 * <code>
 * Optional::nullable($input)
 *     ->map(function ($e) { // Transform $e })
 *     ->or($defaultValue)
 * ;
 * </code>
 */
final class Optional implements OptionalInterface
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * Optional constructor.
     *
     * @param mixed $value
     */
    private function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $predicate)
    {
        if (!$predicate($this->value)) {
            return self::empty();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $transformer)
    {
        return self::nullable($transformer($this->value));
    }

    /**
     * {@inheritdoc}
     */
    public function apply(callable $consumer)
    {
        $consumer($this->value);
    }

    /**
     * {@inheritdoc}
     */
    public function or($value)
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function orSupply(callable $supplier)
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function orThrows($exception = \RuntimeException::class)
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function present()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, array $arguments)
    {
        return self::nullable($this->value->$name(...$arguments));
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        return self::nullable($this->value->$name);
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        return isset($this->value->$name);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        $this->value->$name = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function stream()
    {
        return new SingletonStream($this->value);
    }

    /**
     * Wrap a nullable value into an Optional
     * If the value is null, an empty optional is returned
     *
     * @param mixed $value
     *
     * @return OptionalInterface
     */
    public static function nullable($value)
    {
        if ($value === null) {
            return self::empty();
        }

        return new self($value);
    }

    /**
     * Get an empty Optional instance
     *
     * @return EmptyOptional
     */
    public static function empty()
    {
        return EmptyOptional::instance();
    }

    /**
     * Wrap value into an optional
     * The value MUST not be null
     *
     * @param mixed $value
     *
     * @return Optional
     * @throws \TypeError If null value is given
     */
    public static function of($value)
    {
        if ($value === null) {
            throw new \TypeError('The value should not be null');
        }

        return new Optional($value);
    }
}
