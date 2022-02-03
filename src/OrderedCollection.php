<?php

namespace Bdf\Collection;

use ArrayIterator;
use BadMethodCallException;
use Bdf\Collection\Stream\ArrayStream;
use Bdf\Collection\Stream\StreamInterface;
use OutOfBoundsException;
use function array_merge;
use function array_search;
use function array_slice;
use function count;
use function intdiv;
use function is_array;
use function iterator_to_array;
use function sort;
use function usort;

/**
 * Collection implementation that provides an ordering on its elements
 *
 * This collection is lazy ordered : it sorts elements only when necessary
 *
 * Complexity :
 * - add()      : O(1)      But needs to be sorted after
 * - contains() : O(log(n)) Sort elements if not sorted
 * - remove()   : O(n)      The array is copied, but keep the order
 * - forEach()  : O(n)      Sort elements if not sorted
 * - at()       : O(1)      Sort elements if not sorted
 * - search()   : O(log(n)) Sort elements if not sorted
 *
 * @template T
 * @implements OrderedCollectionInterface<T>
 */
class OrderedCollection implements OrderedCollectionInterface
{
    /**
     * @var callable|null
     */
    private $comparator;

    /**
     * @var array
     */
    private $elements = [];

    /**
     * @var bool
     */
    private $sorted = true;


    /**
     * SortedCollection constructor.
     *
     * @param callable|null $comparator The elements comparator. If null use natural order
     *     The comparator must takes the two elements (A, B) to compare as parameters
     *     And must return an integer as : <= -1 for A < B, = 0 for A = B and >= 1 for A > B
     */
    public function __construct(callable $comparator = null)
    {
        $this->comparator = $comparator;
    }

    /**
     * {@inheritdoc}
     */
    public function add($element): bool
    {
        $this->elements[] = $element;
        $this->sorted = count($this->elements) === 1;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addAll(iterable $elements): bool
    {
        foreach ($elements as $element) {
            $this->elements[] = $element;
        }

        $this->sorted = count($this->elements) <= 1;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function replace(iterable $elements): bool
    {
        $this->elements = is_array($elements) ? array_values($elements) : iterator_to_array($elements, false);
        $this->sorted = count($this->elements) <= 1;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($element, bool $strict = false): bool
    {
        return $this->search($element, $strict) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($element, bool $strict = false): bool
    {
        $key = $this->search($element, $strict);

        if ($key === false) {
            return false;
        }

        $this->offsetUnset($key);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->elements = [];
        $this->sorted = true;
    }

    /**
     * {@inheritdoc}
     */
    public function empty(): bool
    {
        return empty($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function forEach(callable $consumer): void
    {
        $this->sortElements();

        foreach ($this->elements as $position => $element) {
            $consumer($element, $position);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $this->sortElements();

        return $this->elements;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function stream(): StreamInterface
    {
        return new ArrayStream($this->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function search($element, bool $strict = false)
    {
        $this->sortElements();

        $first = 0;
        $last  = count($this->elements) - 1;

        // Until 3000 elements, native array search is faster
        if ($last < 3000) {
            return array_search($element, $this->elements, $strict);
        }

        // Perform binary search
        while ($first <= $last) {
            $key = intdiv($first + $last, 2);
            $current = $this->elements[$key];

            if ((!$strict && $element == $current) || ($strict && $element === $current)) {
                return $key;
            }

            if (($this->comparator && ($this->comparator)($element, $current) < 0) || (!$this->comparator && $element < $current)) {
                $last = $key - 1;
            } else {
                $first = $key + 1;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function at(int $position)
    {
        $this->sortElements();

        if (!isset($this->elements[$position])) {
            throw new OutOfBoundsException('Invalid position '.$position);
        }

        return $this->elements[$position];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        $this->sortElements();

        return isset($this->elements[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        $this->sortElements();

        return $this->elements[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        $this->sortElements();

        $this->elements = array_merge(
            array_slice($this->elements, 0, $offset),
            array_slice($this->elements, $offset + 1)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        if ($offset !== null) {
            throw new BadMethodCallException('Cannot set a value into an OrderedCollection');
        }

        $this->add($value);
    }

    private function sortElements(): void
    {
        if ($this->sorted) {
            return;
        }

        if ($this->comparator) {
            usort($this->elements, $this->comparator);
        } else {
            sort($this->elements);
        }

        $this->sorted = true;
    }
}
