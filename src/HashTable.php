<?php

namespace Bdf\Collection;

use Bdf\Collection\Stream\ArrayCombineStream;
use Bdf\Collection\Util\Hash;

/**
 * Table implementation using an hash table
 *
 * This table allow key of any type, including array or object
 * Like HashSet, a custom hash function can be provided for custom key comparison. Can be useful for create a case insensitive key table
 *
 * By default the used hash function is Hash::compute()
 *
 * This table can be used like any other table implementations (including foreach, and array access), but without key type limitation
 *
 * /!\ Be careful with transformation to array, which can have an unexpected behavior with complex keys
 *     For some operations you may remove keys from the table
 *
 * <code>
 * // Create a case insensitive table
 * $ciTable = new HashTable('strtolower'); // Transform keys to lower case
 *
 * $ciTable->set('Foo', 'bar');
 * $ciTable->get('FOO'); // 'bar'
 *
 * // Use HashTable with multiple-keys indexing using array
 * $table = new HashTable();
 *
 * $table[[123, 'aze']] = new Entity(1);
 * $table[[456, 'rty']] = new Entity(2);
 *
 * $table[123, 'aze']; // Returns Entity(1)
 *
 * // Use object as key
 * $table[new Key()] = 'value';
 * </code>
 *
 * (i) About performance :
 *     - The HashTable is about 2 times slower than ArrayCollection with string key
 *     - Using a complex key (like and array or an object) has no significant impact on performance than using string key
 *     - The memory usage is very dependent on the key (custom hash has the lower usage, array or objects has the higher one)
 */
class HashTable implements TableInterface
{
    /**
     * @var array
     */
    private $keys = [];

    /**
     * @var array
     */
    private $values = [];

    /**
     * @var callable
     */
    private $hashFunction;


    /**
     * HashTable constructor.
     *
     * @param callable $hashFunction The the hash function. Takes as parameter the element to hash, and should return a string
     */
    public function __construct(callable $hashFunction = null)
    {
        $this->hashFunction = $hashFunction ?: [Hash::class, 'compute'];
    }

    /**
     * {@inheritdoc}
     */
    public function remove($element, $strict = false)
    {
        $key = array_search($element, $this->values, $strict);

        if ($key === false) {
            return false;
        }

        unset($this->keys[$key]);
        unset($this->values[$key]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $hash = ($this->hashFunction)($key);

        $this->values[$hash] = $value;
        $this->keys[$hash] = $key;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function &get($key)
    {
        $hash = ($this->hashFunction)($key);

        if (!isset($this->values[$hash])) {
            throw new \OutOfBoundsException('The given key cannot be found into the table');
        }

        return $this->values[$hash];
    }

    /**
     * {@inheritdoc}
     */
    public function add($element)
    {
        throw new \BadMethodCallException('HashTable do not supports adding an elements without specify a key');
    }

    /**
     * {@inheritdoc}
     */
    public function addAll($elements)
    {
        throw new \BadMethodCallException('HashTable do not supports adding an elements without specify a key');
    }

    /**
     * {@inheritdoc}
     */
    public function replace($elements)
    {
        $this->clear();

        $b = true;

        foreach ($elements as $key => $value) {
            $b = $this->set($key, $value) && $b;
        }

        return $b;
    }

    /**
     * {@inheritdoc}
     */
    public function unset($key)
    {
        $hash = ($this->hashFunction)($key);

        if (!isset($this->values[$hash])) {
            return false;
        }

        unset($this->values[$hash]);
        unset($this->keys[$hash]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($element, $strict = false)
    {
        return array_search($element, $this->values, $strict) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasKey($key)
    {
        return isset($this->values[($this->hashFunction)($key)]);
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        return array_values($this->keys);
    }

    /**
     * {@inheritdoc}
     */
    public function values()
    {
        return array_values($this->values);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->values = [];
        $this->keys = [];
    }

    /**
     * {@inheritdoc}
     */
    public function empty()
    {
        return empty($this->keys);
    }

    /**
     * {@inheritdoc}
     *
     * By default, this method try to create an associative array. But this purpose is discouraged because it have undefined behavior with non-scalar keys
     * With the parameter $assoc set to false, an array in form : [ [ Key, Value], ... ] is returned
     *
     * <code>
     * $table = new HashTable();
     * $table->set('foo', 'bar');
     * $table->set('oof', 'baz');
     *
     * $table->toArray();
     * // [ 'foo' => 'bar'
     * //   'oof' => 'baz' ]
     *
     * $table->toArray(false);
     * // [ ['foo', 'bar']
     * //   ['oof', 'baz'] ]
     * </code>
     *
     * @param boolean $assoc If set to true will make associative array, or false to return array in form [ [Key, Value], ... ]
     */
    public function toArray($assoc = true)
    {
        if ($assoc) {
            return array_combine($this->keys, $this->values);
        }

        $array = [];

        foreach ($this->keys as $hash => $key) {
            $array[] = [$key, $this->values[$hash]];
        }

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function stream()
    {
        return new ArrayCombineStream($this->keys, $this->values);
    }

    /**
     * {@inheritdoc}
     */
    public function forEach(callable $consumer)
    {
        foreach ($this->keys as $hash => $key) {
            $consumer($this->values[$hash], $key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->stream();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->hasKey($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function &offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->add($offset);
        } else {
            $this->set($offset, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->unset($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->values);
    }
}
