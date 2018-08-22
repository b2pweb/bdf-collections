<?php

namespace Bdf\Collection\Util\Extension;

use Bdf\Collection\TableInterface;

/**
 * Trait for implements TableInterface delegation objects
 *
 * <code>
 * class MyDelegate implements TableInterface
 * {
 *     use TableDelegationTrait;
 *
 *     public function __construct(TableInterface $collection)
 *     {
 *         $this->setCollection($collection);
 *     }
 * }
 * </code>
 *
 * @see TableInterface
 */
trait TableDelegationTrait
{
    use CollectionDelegationTrait;

    /**
     * @var TableInterface
     */
    private $collection;


    /**
     * Set the inner table instance
     *
     * @param TableInterface $collection
     *
     * @return void
     */
    private function setCollection(TableInterface $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @see TableInterface::set($key, $value)
     */
    public function set($key, $value)
    {
        $this->collection->set($key, $value);
    }

    /**
     * @see TableInterface::&get($key)
     */
    public function &get($key)
    {
        return $this->collection->get($key);
    }

    /**
     * @see TableInterface::hasKey($key)
     */
    public function hasKey($key)
    {
        return $this->collection->hasKey($key);
    }

    /**
     * @see TableInterface::unset($key)
     */
    public function unset($key)
    {
        return $this->collection->unset($key);
    }

    /**
     * @see TableInterface::keys()
     */
    public function keys()
    {
        return $this->collection->keys();
    }

    /**
     * @see TableInterface::values()
     */
    public function values()
    {
        return $this->collection->values();
    }

    /**
     * @see TableInterface::&offsetGet($offset)
     */
    public function &offsetGet($offset)
    {
        return $this->collection[$offset];
    }

    /**
     * @see TableInterface::offsetExists()
     */
    public function offsetExists($offset)
    {
        return isset($this->collection[$offset]);
    }

    /**
     * @see TableInterface::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        $this->collection[$offset] = $value;
    }

    /**
     * @see TableInterface::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }
}
