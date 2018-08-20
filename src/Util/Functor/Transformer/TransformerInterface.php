<?php

namespace Bdf\Collection\Util\Functor\Transformer;

use Bdf\Collection\Stream\StreamInterface;
use Bdf\Collection\Util\OptionalInterface;

/**
 * Transform an element value to a new value
 *
 * @see OptionalInterface::map()
 * @see StreamInterface::map()
 */
interface TransformerInterface
{
    /**
     * Apply the method on an element
     * The implementation should supports if the key is given or not
     *
     * @param mixed $element The element
     * @param mixed $key The element key, if applicable
     *
     * @return mixed The new value
     */
    public function __invoke($element, $key = null);
}
