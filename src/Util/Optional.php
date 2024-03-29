<?php

namespace Bdf\Collection\Util;

use Bdf\Collection\Stream\SingletonStream;
use Bdf\Collection\Stream\StreamInterface;
use RuntimeException;
use TypeError;

/**
 * Handle null values, and create simple null objects
 *
 * <code>
 * Optional::nullable($input)
 *     ->map(function ($e) { // Transform $e })
 *     ->or($defaultValue)
 * ;
 * </code>
 *
 * @template T
 * @implements OptionalInterface<T>
 */
final class Optional implements OptionalInterface
{
    /**
     * @var T
     */
    private $value;

    /**
     * Optional constructor.
     *
     * @param T $value
     */
    private function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $predicate): OptionalInterface
    {
        if (!$predicate($this->value)) {
            return self::empty();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $transformer): OptionalInterface
    {
        return self::nullable($transformer($this->value));
    }

    /**
     * {@inheritdoc}
     */
    public function apply(callable $consumer): void
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
    public function orThrows($exception = RuntimeException::class)
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function present(): bool
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
    public function __call(string $name, array $arguments): OptionalInterface
    {
        return self::nullable($this->value->$name(...$arguments));
    }

    /**
     * {@inheritdoc}
     */
    public function __get(string $name): OptionalInterface
    {
        return self::nullable($this->value->$name);
    }

    /**
     * {@inheritdoc}
     */
    public function __isset(string $name): bool
    {
        return isset($this->value->$name);
    }

    /**
     * {@inheritdoc}
     */
    public function __set(string $name, $value): void
    {
        $this->value->$name = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function stream(): StreamInterface
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
    public static function nullable($value): OptionalInterface
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
    public static function empty(): EmptyOptional
    {
        return EmptyOptional::instance();
    }

    /**
     * Wrap value into an optional
     * The value MUST not be null
     *
     * @param R $value
     *
     * @return Optional<R>
     * @throws TypeError If null value is given
     *
     * @template R
     */
    public static function of($value): Optional
    {
        if ($value === null) {
            throw new TypeError('The value should not be null');
        }

        return new Optional($value);
    }
}
