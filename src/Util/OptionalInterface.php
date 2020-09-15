<?php

namespace Bdf\Collection\Util;

use Bdf\Collection\Stream\Streamable;
use RuntimeException;
use Throwable;

/**
 * Wrap an optional (nullable) value into an object
 * Optional is a way for handling null values, and create simple null objects
 *
 * @template T
 */
interface OptionalInterface extends Streamable
{
    /**
     * Filter the optional value
     *
     * <code>
     * $opt
     *     ->filter(function ($e) { return $e[0] === 'A'; })
     *     ->apply(function ($e) { ... })
     * ;
     * </code>
     *
     * @param callable(T):bool $predicate A predicate function which contains the element as parameter, and return true or false
     *
     * @return OptionalInterface<T>
     */
    public function filter(callable $predicate): OptionalInterface;

    /**
     * Transform the element if it's present
     *
     * - If the current Optional is empty, will return an empty Optional
     * - If the transformer returns null, will also return an empty Optional
     * - Else wrap the result value into an Optional
     *
     * The map() method can be chained, also with first() method
     *
     * <code>
     * $opt->map(function ($obj) {
     *     return $obj->get();
     * });
     * </code>
     *
     * @template R
     * @param callable(T):R $transformer The transformer function. Take as parameter the element and return the transformed value
     *
     * @return OptionalInterface<R> The result wrap into an optional
     */
    public function map(callable $transformer): OptionalInterface;

    /**
     * Apply the consumer on the element if it's present
     *
     * <code>
     * $opt->apply(function ($e) {
     *     // $e is present and not null
     * });
     * </code>
     *
     * @param callable(T):void $consumer
     *
     * @return void
     */
    public function apply(callable $consumer): void;

    /**
     * Get the current Optional value if it's present, or the parameter value if not present
     *
     * <code>
     * Optional::empty()->or(123); // Return 123
     * Optional::of(456)->or(123); // Return 456
     * </code>
     *
     * @param T $value The default value
     *
     * @return T
     */
    public function or($value);

    /**
     * Get the current value if present, or throws an exception
     *
     * @param class-string<Throwable>|Throwable $exception The exception instance or class
     *
     * @return T The stored value
     *
     * @throws Throwable
     */
    public function orThrows($exception = RuntimeException::class);

    /**
     * Get the current value if present, or return the supplier result
     *
     * <code>
     * Optional::of(456)->orSupply('rand'); // Return 456
     * Optional::empty()->orSupply('rand'); // Return random number
     * </code>
     *
     * @param callable():T $supplier Generate the default value
     *
     * @return T
     */
    public function orSupply(callable $supplier);

    /**
     * Check if the Optional value is present or not
     *
     * @return boolean
     */
    public function present(): bool;

    /**
     * Get the current stored value
     *
     * @return T|null
     */
    public function get();

    /**
     * Delegate call to contained object if present, and wrap the return value into an Optional
     *
     * @param string $name The method name
     * @param array $arguments The method parameters
     *
     * @return OptionalInterface
     */
    public function __call(string $name, array $arguments): OptionalInterface;

    /**
     * Get a property from the contained object if present, and wrap the value into an Optional
     *
     * @param string $name The property name
     *
     * @return OptionalInterface
     */
    public function __get(string $name): OptionalInterface;

    /**
     * Check if the contained object contains the given property
     * If the Optional is empty will always return false
     *
     * @param string $name
     *
     * @return boolean
     */
    public function __isset(string $name): bool;

    /**
     * Set the contains object property value if it's present
     *
     * @param string $name The property name
     * @param mixed $value The new value
     *
     * @return void
     */
    public function __set(string $name, $value): void;
}
