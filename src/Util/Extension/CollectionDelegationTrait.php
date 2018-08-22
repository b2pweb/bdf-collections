<?php

namespace Bdf\Collection\Util\Extension;

use Bdf\Collection\CollectionInterface;

/**
 * Trait for implements CollectionInterface delegation objects
 *
 * <code>
 * class MyDelegate implements CollectionInterface
 * {
 *     use CollectionDelegationTrait;
 *
 *     public function __construct(CollectionInterface $collection)
 *     {
 *         $this->setCollection($collection);
 *     }
 * }
 * </code>
 *
 * @see CollectionInterface
 */
trait CollectionDelegationTrait
{
    /**
     * @var CollectionInterface
     */
    private $collection;


    /**
     * Set the inner collection
     *
     * @param CollectionInterface $collection
     *
     * @return void
     */
    private function setCollection(CollectionInterface $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @see CollectionInterface::add()
     */
    public function add($element)
    {
        return $this->collection->add($element);
    }

    /**
     * @see CollectionInterface::addAll()
     */
    public function addAll($elements)
    {
        return $this->collection->addAll($elements);
    }

    /**
     * @see CollectionInterface::replace()
     */
    public function replace($elements)
    {
        return $this->collection->replace($elements);
    }

    /**
     * @see CollectionInterface::remove()
     */
    public function remove($element, $strict = false)
    {
        return $this->collection->remove($element, $strict);
    }

    /**
     * @see CollectionInterface::clear()
     */
    public function clear()
    {
        $this->collection->clear();
    }

    /**
     * @see CollectionInterface::empty()
     */
    public function empty()
    {
        return $this->collection->empty();
    }

    /**
     * @see CollectionInterface::contains()
     */
    public function contains($element, $strict = false)
    {
        return $this->collection->contains($element, $strict);
    }

    /**
     * @see CollectionInterface::toArray()
     */
    public function toArray()
    {
        return $this->collection->toArray();
    }

    /**
     * @see CollectionInterface::count()
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * @see CollectionInterface::getIterator()
     */
    public function getIterator()
    {
        return $this->collection->getIterator();
    }

    /**
     * @see CollectionInterface::forEach()
     */
    public function forEach(callable $consumer)
    {
        $this->collection->forEach($consumer);
    }

    /**
     * @see CollectionInterface::stream()
     */
    public function stream()
    {
        return $this->collection->stream();
    }
}
