<?php

namespace Bdf\Collection;

/**
 * An OrderedCollection keeps all its elements in order
 */
interface OrderedCollectionInterface extends CollectionInterface, \ArrayAccess
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
     * @return mixed
     * @throws \OutOfBoundsException When position is less than zero, or do not exists
     */
    public function at($position);

    /**
     * {@inheritdoc}
     *
     * Cannot be called on an ordered collection
     */
    public function offsetSet($offset, $value);
}
