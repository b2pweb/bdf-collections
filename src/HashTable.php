<?php

namespace Bdf\Collection;

use BadMethodCallException;
use Bdf\Collection\Stream\ArrayCombineStream;
use Bdf\Collection\Stream\StreamInterface;
use Bdf\Collection\Util\Hash;
use OutOfBoundsException;
use function array_combine;
use function array_search;
use function array_values;
use function count;

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
 *
 * @template K
 * @template T
 * @implements TableInterface<K, T>
 */
class HashTable implements TableInterface
{
    /**
     * @var K[]
     */
    private $keys = [];

    /**
     * @var T[]
     */
    private $values = [];

    /**
     * @var callable(K):array-key
     */
    private $hashFunction;


    /**
     * HashTable constructor.
     *
     * @param callable(K):array-key|null $hashFunction The the hash function. Takes as parameter the element to hash, and should return a string
     */
    public function __construct(?callable $hashFunction = null)
    {
        $this->hashFunction = $hashFunction ?: [Hash::class, 'compute'];
    }

    /**
     * {@inheritdoc}
     */
    public function remove($element, bool $strict = false): bool
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
    public function set($key, $value): void
    {
        $hash = ($this->hashFunction)($key);

        $this->values[$hash] = $value;
        $this->keys[$hash] = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function &get($key)
    {
        $hash = ($this->hashFunction)($key);

        if (!isset($this->values[$hash])) {
            throw new OutOfBoundsException('The given key cannot be found into the table');
        }

        return $this->values[$hash];
    }

    /**
     * {@inheritdoc}
     */
    public function add($element): bool
    {
        throw new BadMethodCallException('HashTable do not supports adding an elements without specify a key');
    }

    /**
     * {@inheritdoc}
     */
    public function addAll(iterable $elements): bool
    {
        throw new BadMethodCallException('HashTable do not supports adding an elements without specify a key');
    }

    /**
     * {@inheritdoc}
     */
    public function replace(iterable $elements): bool
    {
        $this->clear();

        foreach ($elements as $key => $value) {
            $this->set($key, $value);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function unset($key): bool
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
    public function contains($element, bool $strict = false): bool
    {
        return array_search($element, $this->values, $strict) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasKey($key): bool
    {
        return isset($this->values[($this->hashFunction)($key)]);
    }

    /**
     * {@inheritdoc}
     */
    public function keys(): array
    {
        return array_values($this->keys);
    }

    /**
     * {@inheritdoc}
     */
    public function values(): array
    {
        return array_values($this->values);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->values = [];
        $this->keys = [];
    }

    /**
     * {@inheritdoc}
     */
    public function empty(): bool
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
    public function toArray(bool $assoc = true): array
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
    public function stream(): StreamInterface
    {
        return new ArrayCombineStream($this->keys, $this->values);
    }

    /**
     * {@inheritdoc}
     */
    public function forEach(callable $consumer): void
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
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->add($value);
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
    public function count(): int
    {
        return count($this->values);
    }
}
