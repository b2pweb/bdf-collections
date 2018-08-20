<?php

namespace Bdf\Collection\Util;

/**
 * Hash utils class
 */
final class Hash
{
    /**
     * Compute hash code of a value
     *
     * @param mixed $value
     *
     * @return integer
     */
    public static function compute($value)
    {
        if ($value instanceof Hashable) {
            return 'O:'.$value->hash();
        }

        return serialize($value);
    }
}
