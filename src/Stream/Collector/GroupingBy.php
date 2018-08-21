<?php

namespace Bdf\Collection\Stream\Collector;

use Bdf\Collection\HashTable;
use Bdf\Collection\TableInterface;
use Bdf\Collection\Util\Functor\Transformer\Getter;
use Bdf\Collection\Util\Hash;

/**
 * Grouping elements by an extracted key
 *
 * Elements with same key will be append to an array
 *
 * <code>
 * $stream = new ArrayStream([
 *     new Person('John', 'Doe'),
 *     new Person('John', 'Smith'),
 *     new Person('Mickey', 'Mouse'),
 *     new Person('Donald', 'Duck'),
 * ]);
 *
 * $stream->collect(new GroupingBy(new Getter('firstName')));
 * // Result : Array [
 * //     'John'   => [ Person('John', 'Doe'), Person('John', 'Smith') ],
 * //     'Mickey' => [ Person('Mickey', 'Mouse') ],
 * //     'Donald' => [ Person('Donald', 'Duck') ], ]
 * </code>
 */
final class GroupingBy implements CollectorInterface
{
    /**
     * @var callable
     */
    private $getter;

    /**
     * @var bool
     */
    private $preserveKeys;

    /**
     * @var TableInterface|array
     */
    private $table;


    /**
     * GroupingBy constructor.
     *
     * @param callable $getter Extract the group key from element
     * @param bool $preserveKeys Preserve the keys on group array
     * @param array|TableInterface $table The result table or array
     */
    public function __construct(callable $getter, $preserveKeys = false, $table = [])
    {
        $this->getter = $getter;
        $this->preserveKeys = $preserveKeys;
        $this->table = $table;
    }

    /**
     * {@inheritdoc}
     */
    public function aggregate($element, $key = null)
    {
        $groupKey = ($this->getter)($element);

        if (!isset($this->table[$groupKey])) {
            $this->table[$groupKey] = [];
        }

        if ($this->preserveKeys) {
            $this->table[$groupKey][$key] = $element;
        } else {
            $this->table[$groupKey][] = $element;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finalize()
    {
        return $this->table;
    }

    /**
     * Grouping elements by a scalar key (integer or string)
     *
     * <code>
     * // Perform the same operation
     * $stream->collection(GroupingBy::scalar(function ($entity) { return $entity->firstName(); });
     * $stream->collection(GroupingBy::scalar(new Getter('firstName'));
     * $stream->collection(GroupingBy::scalar('firstName'));
     * </code>
     *
     * @param string|callable $getter The key getter function or name
     *
     * @return GroupingBy
     */
    public static function scalar($getter)
    {
        return new GroupingBy(is_callable($getter) ? $getter : new Getter($getter));
    }

    /**
     * Indexing elements by a complex type key like array or object
     *
     * <code>
     * // Grouping by multiple key values
     * $stream->collection(GroupingBy::hash(function ($e) { return [$e->pk1(), $e->pk2()]; }));
     *
     * // Grouping by object
     * $stream->collection(GroupingBy::hash(new Getter('embeddedEntity'));
     * </code>
     *
     * @param callable $getter The key getter
     * @param bool $preserveKeys Preserve the key on the group array
     * @param callable|null $hashFunction The hash function, which will be applied to the key value. By default use Hash::compute
     *
     * @return GroupingBy
     *
     * @see Hash::compute()
     */
    public static function hash(callable $getter, $preserveKeys = false, callable $hashFunction = null)
    {
        return new GroupingBy($getter, $preserveKeys, new HashTable($hashFunction));
    }
}
