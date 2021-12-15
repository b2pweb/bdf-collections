<?php

namespace Bdf\Collection;

use Bdf\Collection\Stream\ArrayStream;
use Bdf\Collection\Stream\StreamInterface;
use Bdf\Collection\Util\Hash;
use Bdf\Collection\Util\Optional;
use Bdf\Collection\Util\OptionalInterface;
use function array_values;
use function count;

/**
 * Set implementation using an hash table
 *
 * This set implementation handle scalar value, objects and arrays
 * Two elements are considered as equals when there hash values are equals
 *
 * By default the used hash function is Hash::compute(), but you can define a custom hash function in constructor
 *
 * /!\ Because the hash function is used for comparisons, the $strict parameter in methods remove() and contains() is not used
 *
 * <code>
 * $set = new HashSet();
 *
 * $set->add(new Person('Mickey', 'Mouse')); // true
 * $set->add(new Person('Mickey', 'Mouse')); // false : Mickey is already added
 * $set->contains(new Person('Mickey', 'Mouse')); // true
 *
 * $set->add(new Person('John', 'Doe')); // true
 * $set->add(new Person('John', 'Smith')); // true
 *
 * $setWithCustomHash = new HashSet(function ($person) { return $person->firstName(); }); // Compare only the first name
 *
 * $setWithCustomHash->add(new Person('John', 'Doe'));
 *
 * $setWithCustomHash->contains(new Person('John', 'Smith')); // true : The hash function consider only the first name
 * $setWithCustomHash->add(new Person('John', 'Smith')); // false : Considered as already added !
 * </code>
 *
 * /!\ The default hash function distinguish by type and value, so int(123) is not equals with string('123')
 *     If you want to compare without consider the type, you must define a custom hash function, for example : `function ($e) { return (string) $e; }`
 *
 * Ex:
 * <code>
 * $set = new HashSet();
 * $set->add(123);
 * $set->contains(123); // true
 * $set->contains('123'); // false
 * <code>
 *
 * (i) About performance :
 *     - add() + toArray() has linear complexity (adding 10x more elements takes 10x more time), whereas array_unique() has O(n.log(n)) complexity
 *     - contains() is about 3 times faster than array_search() on 10k elements
 *     - For 10k elements HashSet add() + toArray() vs array_unique :
 *         - With integers, HashSet about 2 times slower
 *         - With objects without custom hash, HashSet is about 30% slower
 *         - With objects with custom hash, HashSet has same performances
 *
 * @see Hash::compute() The default hash function
 *
 * @template T
 * @implements SetInterface<T>
 */
class HashSet implements SetInterface
{
    use CollectionTrait;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var callable
     */
    private $hashFunction;


    /**
     * HashSet constructor.
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
    public function add($element): bool
    {
        $index = ($this->hashFunction)($element);

        if (isset($this->data[$index])) {
            return false;
        }

        $this->data[$index] = $element;
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($element, bool $strict = false): bool
    {
        $key = ($this->hashFunction)($element);

        if (!isset($this->data[$key])) {
            return false;
        }

        return !$strict || $this->data[$key] === $element;
    }

    /**
     * {@inheritdoc}
     */
    public function lookup($element): OptionalInterface
    {
        $index = ($this->hashFunction)($element);

        if (!isset($this->data[$index])) {
            return Optional::empty();
        }

        return Optional::of($this->data[$index]);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($element, bool $strict = false): bool
    {
        $index = ($this->hashFunction)($element);

        if (!isset($this->data[$index])) {
            return false;
        }

        if (!$strict || $this->data[$index] === $element) {
            unset($this->data[$index]);
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * {@inheritdoc}
     */
    public function empty(): bool
    {
        return empty($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function forEach(callable $consumer): void
    {
        foreach ($this->data as $value) {
            $consumer($value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_values($this->data);
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
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function stream(): StreamInterface
    {
        return new ArrayStream(array_values($this->data));
    }

    /**
     * Create an HashSet with spl_object_hash as hash function
     *
     * @return HashSet
     *
     * @see spl_object_hash()
     */
    public static function spl(): HashSet
    {
        return new static('spl_object_hash');
    }
}
