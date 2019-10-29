<?php

namespace Bdf\Collection\Util;

use function serialize;

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
     * @return string
     */
    public static function compute($value): string
    {
        if ($value instanceof Hashable) {
            return 'O:'.$value->hash();
        }

        return serialize($value);
    }
}
