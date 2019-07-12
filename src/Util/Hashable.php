<?php

namespace Bdf\Collection\Util;

/**
 * Define custom hash method on objects
 *
 * <code>
 * class User implements Hashable
 * {
 *     private $id;
 *     private $name;
 *     private $password;
 *
 *     public function hash()
 *     {
 *         return $this->id; // Compute hash only on ID
 *     }
 * }
 * </code>
 */
interface Hashable
{
    /**
     * Compute the object hash code
     * If two objects have the same hash, there considered as equals by hash tables
     *
     * /!\ Properties used for compute the hash should be immutable, or memory leaks may occurs
     *     If the hash of an object change, it will no longer be accessible (for find AND for remove) from hash collections
     *
     * @return string|integer
     */
    public function hash();
}
