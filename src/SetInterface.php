<?php

namespace Bdf\Collection;

use Bdf\Collection\Util\OptionalInterface;

/**
 * A set is a collection with ensuring that contains no duplicate elements
 *
 * @template T
 * @extends CollectionInterface<T>
 */
interface SetInterface extends CollectionInterface
{
    /**
     * Find the corresponding elements stored into the Set
     *
     * <code>
     * $set = new HashSet();
     * $set->add($john = new Person('John', 'Doe'));
     *
     * $set->lookup(new Person('John', 'Doe'))->get() === $john; // Get the added object
     * </code>
     *
     * @param T $element The element to find
     *
     * @return OptionalInterface<T> The element wrap into an Optional if found, or an empty Optional
     */
    public function lookup($element): OptionalInterface;
}
