<?php

namespace Bdf\Collection\Stream\Collector;

use Bdf\Collection\ArrayCollection;
use Bdf\Collection\HashTable;
use Bdf\Collection\TableInterface;
use Bdf\Collection\Util\Functor\Transformer\Getter;
use Bdf\Collection\Util\Hash;

/**
 * Indexing elements by an extracted key
 * Only one occurrence can be set for one key. If two elements have the same key, the last one will override the first one
 * It's advisable to perform a StreamInterface::distinct() using the same getter before indexing the elements if there is possible duplicate keys
 *
 * <code>
 * $stream = new ArrayStream([
 *     new Person('John', 'Doe'),
 *     new Person('Mickey', 'Mouse'),
 *     new Person('Donald', 'Duck'),
 * ]);
 *
 * $stream->collect(new IndexingBy(new Getter('firstName')));
 * // Result : ArrayCollection [
 * //     'John'   => Person('John', 'Doe'),
 * //     'Mickey' => Person('Mickey', 'Mouse'),
 * //     'Donald' => Person('Donald', 'Duck'), ]
 * </code>
 */
final class IndexingBy implements CollectorInterface
{
    /**
     * @var callable
     */
    private $getter;

    /**
     * @var TableInterface
     */
    private $table;


    /**
     * GroupingBy constructor.
     *
     * @param callable $getter Extract the group key from element
     * @param TableInterface $table The result table
     */
    public function __construct(callable $getter, TableInterface $table = null)
    {
        $this->getter = $getter;
        $this->table = $table ?: new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function aggregate($element, $key = null)
    {
        $this->table[($this->getter)($element)] = $element;
    }

    /**
     * {@inheritdoc}
     */
    public function finalize()
    {
        return $this->table;
    }

    /**
     * Indexing elements by a scalar key (integer or string)
     *
     * <code>
     * // Perform the same operation
     * $stream->collection(IndexingBy::scalar(function ($entity) { return $entity->firstName(); });
     * $stream->collection(IndexingBy::scalar(new Getter('firstName'));
     * $stream->collection(IndexingBy::scalar('firstName'));
     * </code>
     *
     * @param string|callable $getter The key getter function or name
     *
     * @return IndexingBy
     */
    public static function scalar($getter)
    {
        return new IndexingBy(is_callable($getter) ? $getter : new Getter($getter));
    }

    /**
     * Indexing elements by a complex type key like array or object
     *
     * <code>
     * // Indexing by multiple key values
     * $stream->collection(IndexingBy::hash(function ($e) { return [$e->pk1(), $e->pk2()]; }));
     *
     * // Indexing by object
     * $stream->collection(IndexingBy::hash(new Getter('embeddedEntity'));
     * </code>
     *
     * @param callable $getter The key getter
     * @param callable|null $hashFunction The hash function, which will be applied to the key value. By default use Hash::compute
     *
     * @return IndexingBy
     *
     * @see Hash::compute()
     */
    public static function hash(callable $getter, callable $hashFunction = null)
    {
        return new IndexingBy($getter, new HashTable($hashFunction));
    }
}
