<?php

namespace Bdf\Collection;

use Bdf\Collection\Stream\Streamable;

/**
 * Base collection type
 *
 * A collection is a container of elements.
 * The elements can be added, removed, iterated, and check if its contained
 */
interface CollectionInterface extends \IteratorAggregate, \Countable, Streamable
{
    /**
     * Add a new element into the collection
     *
     * @param mixed $element
     *
     * @return boolean True on success, or false is failed with implementation constraints
     */
    public function add($element);

    /**
     * Add all elements to the collection
     *
     * @param array|\Traversable $elements Elements to add. Can be an array or any traversable object
     *
     * @return boolean True on success, or false is at least one elements failed to add
     */
    public function addAll($elements);

    /**
     * Clear the collections and replace all elements with new elements
     * This method is equivalent to :
     *
     * <code>
     * $collection->clear();
     * $collection->addAll($elements);
     * </code>
     *
     * @param array|\Traversable $elements The elements to add
     *
     * @return boolean True on success, or false is at least one elements failed to add
     */
    public function replace($elements);

    /**
     * Check if the collection contains the given element
     *
     * @param mixed $element Element to check
     * @param boolean $strict Do a strict comparison
     *
     * @return boolean True is the element is found into the collection
     */
    public function contains($element, $strict = false);

    /**
     * Remove an element from the collection
     * Only the first matching element is removed
     *
     * @param mixed $element Element to remove
     * @param boolean $strict Do a strict comparison
     *
     * @return boolean True if the element is found and successfully removed
     */
    public function remove($element, $strict = false);

    /**
     * Remove all data from the collection
     * After this call, the collection will be empty
     *
     * @return void
     */
    public function clear();

    /**
     * Check if the collection is empty
     * A collection is empty if and only if its size is equals to zero.
     *
     * This method is equivalent with : `$collection->count() === 0`
     *
     * @return boolean
     */
    public function empty();

    /**
     * Apply $consumer on each elements of the collection
     *
     * The consumer should have one parameter for the element value
     *
     * Ex:
     * <code>
     * $collection->forEach(function ($element) {
     *     $element->doSomething();
     * });
     * </code>
     *
     * @param callable $consumer Function to apply
     *
     * @return void
     */
    public function forEach(callable $consumer);

    /**
     * Get the native array value of the collection
     *
     * @return array
     */
    public function toArray();
}
