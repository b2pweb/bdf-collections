<?php

namespace Bdf\Collection\Util\Functor\Predicate;

use Bdf\Collection\Stream\StreamInterface;
use Bdf\Collection\Util\OptionalInterface;

/**
 * Check an element value
 *
 * @see OptionalInterface::filter()
 * @see StreamInterface::filter()
 */
interface PredicateInterface
{
    /**
     * Check the element value
     * The implementation should supports if the key is given or not
     *
     * @param mixed $element The element
     * @param mixed $key The element key, if applicable
     *
     * @return boolean true if match, or false
     */
    public function __invoke($element, $key = null): bool;
}
