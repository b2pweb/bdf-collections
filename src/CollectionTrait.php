<?php

namespace Bdf\Collection;

/**
 * Base implementation of CollectionInterface methods
 */
trait CollectionTrait
{
    /**
     * @see CollectionInterface::addAll()
     */
    public function addAll($elements)
    {
        $b = true;

        foreach ($elements as $item) {
            $b = $this->add($item) && $b;
        }

        return $b;
    }

    /**
     * @see CollectionInterface::replace()
     */
    public function replace($elements)
    {
        $this->clear();

        return $this->addAll($elements);
    }
}
