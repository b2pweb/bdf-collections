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
    private function setCollection(TableInterface $collection): void
    {
        $this->collection = $collection;
    }

    /**
     * @see TableInterface::set($key, $value)
     */
    public function set($key, $value): void
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
    public function hasKey($key): bool
    {
        return $this->collection->hasKey($key);
    }

    /**
     * @see TableInterface::unset($key)
     */
    public function unset($key): bool
    {
        return $this->collection->unset($key);
    }

    /**
     * @see TableInterface::keys()
     */
    public function keys(): array
    {
        return $this->collection->keys();
    }

    /**
     * @see TableInterface::values()
     */
    public function values(): array
    {
        return $this->collection->values();
    }

    /**
     * @see TableInterface::&offsetGet($offset)
     */
    #[\ReturnTypeWillChange]
    public function &offsetGet($offset)
    {
        return $this->collection[$offset];
    }

    /**
     * @see TableInterface::offsetExists()
     */
    public function offsetExists($offset): bool
    {
        return isset($this->collection[$offset]);
    }

    /**
     * @see TableInterface::offsetSet()
     */
    public function offsetSet($offset, $value): void
    {
        $this->collection[$offset] = $value;
    }

    /**
     * @see TableInterface::offsetUnset()
     */
    public function offsetUnset($offset): void
    {
        unset($this->collection[$offset]);
    }
}
