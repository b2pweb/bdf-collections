<?php

namespace Bdf\Collection;

use ArrayAccess;

/**
 * A table is a key-value collection
 *
 * Each elements of the table will be attached to a key
 *
 * @template K
 * @template T
 * @extends CollectionInterface<T>
 */
interface TableInterface extends CollectionInterface, ArrayAccess
{
    /**
     * Set a value to the table with a key
     *
     * @param K $key The key where the value will be stored
     * @param T $value The value to store
     *
     * @return void
     */
    public function set($key, $value): void;

    /**
     * Get a value at the specified index
     *
     * @param K $key The key to search
     *
     * @return T
     *
     * @throws \OutOfBoundsException When cannot found the element at the given key
     */
    public function &get($key);

    /**
     * {@inheritdoc}
     *
     * The element will be store at a generated key, like an increment
     * Some implementation may not supports generation of key, and this method will return false, without store the value
     *
     * @param T $element
     */
    public function add($element): bool;

    /**
     * {@inheritdoc}
     *
     * @param iterable<K, T> $elements
     */
    public function addAll(iterable $elements): bool;

    /**
     * {@inheritdoc}
     *
     * @param iterable<K, T> $elements
     */
    public function replace(iterable $elements): bool;

    /**
     * Check if the table has the given key
     *
     * @param K $key The key to check
     *
     * @return boolean true if the table has the key
     */
    public function hasKey($key): bool;

    /**
     * Remove an element at the given key
     *
     * @param K $key The key to remove
     *
     * @return boolean true if the key exists, and the element is successfully removed
     */
    public function unset($key): bool;

    /**
     * Get all the keys of the table
     *
     * @return K[]
     */
    public function keys(): array;

    /**
     * Get all values (elements) of the table
     *
     * @return T[]
     */
    public function values(): array;

    /**
     * {@inheritdoc}
     *
     * The consumer should have two parameters :
     * - The element
     * - The key
     *
     * Ex:
     * <code>
     * $collection->forEach(function ($element, $key) {
     *     $element->doSomething();
     * });
     * </code>
     *
     * @param callable(T, K=):void $consumer
     */
    public function forEach(callable $consumer): void;

    /**
     * {@inheritdoc}
     *
     * @param K $offset
     * @param T $value
     *
     * @return void
     */
    public function offsetSet($offset, $value): void;

    /**
     * {@inheritdoc}
     *
     * @param K $offset
     * @return T
     */
    #[\ReturnTypeWillChange]
    public function &offsetGet($offset);
}
