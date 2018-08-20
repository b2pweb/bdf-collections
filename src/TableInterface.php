<?php

namespace Bdf\Collection;

/**
 * A table is a key-value collection
 *
 * Each elements of the table will be attached to a key
 */
interface TableInterface extends CollectionInterface, \ArrayAccess
{
    /**
     * Set a value to the table with a key
     *
     * @param mixed $key The key where the value will be stored
     * @param mixed $value The value to store
     *
     * @return void
     */
    public function set($key, $value);

    /**
     * Get a value at the specified index
     *
     * @param mixed $key The key to search
     *
     * @return mixed
     *
     * @throws \OutOfBoundsException When cannot found the element at the given key
     */
    public function &get($key);

    /**
     * {@inheritdoc}
     *
     * The element will be store at a generated key, like an increment
     * Some implementation may not supports generation of key, and this method will return false, without store the value
     */
    public function add($element);

    /**
     * Check if the table has the given key
     *
     * @param mixed $key The key to check
     *
     * @return boolean true if the table has the key
     */
    public function hasKey($key);

    /**
     * Remove an element at the given key
     *
     * @param mixed $key The key to remove
     *
     * @return boolean true if the key exists, and the element is successfully removed
     */
    public function unset($key);

    /**
     * Get all the keys of the table
     *
     * @return array
     */
    public function keys();

    /**
     * Get all values (elements) of the table
     *
     * @return array
     */
    public function values();

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
     */
    public function forEach(callable $consumer);

    /**
     * {@inheritdoc}
     */
    public function &offsetGet($offset);
}
