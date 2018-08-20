<?php

namespace Bdf\Collection\Util;

use Bdf\Collection\Stream\Streamable;

/**
 * Wrap an optional (nullable) value into an object
 * Optional is a way for handling null values, and create simple null objects
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
     * @param callable $predicate A predicate function which contains the element as parameter, and return true or false
     *
     * @return OptionalInterface
     */
    public function filter(callable $predicate);

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
     * @param callable $transformer The transformer function. Take as parameter the element and return the transformed value
     *
     * @return OptionalInterface The result wrap into an optional
     */
    public function map(callable $transformer);

    /**
     * Apply the consumer on the element if it's present
     *
     * <code>
     * $opt->apply(function ($e) {
     *     // $e is present and not null
     * });
     * </code>
     *
     * @param callable $consumer
     *
     * @return void
     */
    public function apply(callable $consumer);

    /**
     * Get the current Optional value if it's present, or the parameter value if not present
     *
     * <code>
     * Optional::empty()->or(123); // Return 123
     * Optional::of(456)->or(123); // Return 456
     * </code>
     *
     * @param mixed $value The default value
     *
     * @return mixed
     */
    public function or($value);

    /**
     * Get the current value if present, or throws an exception
     *
     * @param string|\Throwable $exception The exception instance or class
     *
     * @return mixed The stored value
     *
     * @throws \Throwable
     */
    public function orThrows($exception = \RuntimeException::class);

    /**
     * Check if the Optional value is present or not
     *
     * @return boolean
     */
    public function present();

    /**
     * Get the current stored value
     *
     * @return mixed
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
    public function __call($name, array $arguments);

    /**
     * Get a property from the contained object if present, and wrap the value into an Optional
     *
     * @param string $name The property name
     *
     * @return OptionalInterface
     */
    public function __get($name);

    /**
     * Check if the contained object contains the given property
     * If the Optional is empty will always return false
     *
     * @param string $name
     *
     * @return boolean
     */
    public function __isset($name);

    /**
     * Set the contains object property value if it's present
     *
     * @param string $name The property name
     * @param string $value The new value
     *
     * @return void
     */
    public function __set($name, $value);
}
