<?php

namespace Bdf\Collection\Util\Functor\Consumer;

use Bdf\Collection\CollectionInterface;
use Bdf\Collection\Stream\StreamInterface;
use Bdf\Collection\Util\OptionalInterface;

/**
 * Represent operation on a single element, with its key (if applicable)
 *
 * @see OptionalInterface::apply()
 * @see StreamInterface::forEach()
 * @see CollectionInterface::forEach()
 */
interface ConsumerInterface
{
    /**
     * Apply the method on an element
     * The implementation should supports if the key is given or not
     *
     * @param mixed $element The element
     * @param mixed $key The element key, if applicable
     *
     * @return void
     */
    public function __invoke($element, $key = null): void;
}
