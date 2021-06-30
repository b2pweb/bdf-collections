<?php

namespace Bdf\Collection;

use ArrayAccess;

/**
 * An OrderedCollection keeps all its elements in order
 *
 * @template T
 * @extends CollectionInterface<T>
 */
interface OrderedCollectionInterface extends CollectionInterface, ArrayAccess
{
    /**
     * Search the element position
     *
     * @param mixed $element Element to search
     * @param bool $strict Perform a strict comparison
     *
     * @return false|int The element position, or false if not found
     */
    public function search($element, bool $strict = false);

    /**
     * Get the element at the given position
     *
     * @param integer $position The position to find
     *
     * @return T
     * @throws \OutOfBoundsException When position is less than zero, or do not exists
     */
    public function at(int $position);

    /**
     * {@inheritdoc}
     *
     * Cannot be called on an ordered collection
     */
    public function offsetSet($offset, $value);

    /**
     * {@inheritdoc}
     *
     * The consumer should have two parameters :
     * - The element
     * - The position
     *
     * Ex:
     * <code>
     * $collection->forEach(function ($element, $position) {
     *     $element->doSomething();
     * });
     * </code>
     *
     * @param callable(T, int=):void $consumer
     */
    public function forEach(callable $consumer): void;
}
