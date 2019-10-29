<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Util\Optional;
use Bdf\Collection\Util\OptionalInterface;
use InvalidArgumentException;
use Iterator;
use function array_combine;
use function array_values;
use function current;
use function key;
use function next;
use function reset;

/**
 * Make a stream with combine keys and values
 * Works like PHP array_combine() function, but create a stream instead of an array
 * This streams works with any type of key, like objects or arrays
 *
 * /!\ Some methods can have an unexpected behavior with complex keys
 *     The preserve keys parameter must be set to false for :
 *     - sort() : An array is created before sorting it
 *     - toArray() : Cannot create an array with complex key
 *
 * <code>
 * $stream = new ArrayCombineStream(
 *     [new Geoloc(14.23, -5.02), new Geoloc(12.6, 1.25)],
 *     [new Location(1), new Location(2)]
 * );
 *
 * foreach ($stream as $geoloc => $location) {
 *     // First iteration :  $geoloc = Geoloc(14.23, -5.02), $location = Location(1)
 *     // Second iteration : $geoloc = Geoloc(12.6, 1.25),   $location = Location(2)
 * }
 * </code>
 *
 * @see array_combine()
 */
final class ArrayCombineStream implements Iterator, StreamInterface
{
    use StreamTrait;

    /**
     * @var array
     */
    private $keys;

    /**
     * @var array
     */
    private $values;


    /**
     * ArrayCombineStream constructor.
     *
     * The two parameters must be two arrays of same size
     *
     * @param array $keys The stream keys
     * @param array $values The stream values
     */
    public function __construct(array $keys, array $values)
    {
        if (count($keys) !== count($values)) {
            throw new InvalidArgumentException('The two arrays have different size');
        }

        $this->keys = $keys;
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(bool $preserveKeys = true): array
    {
        return $preserveKeys
            ? array_combine($this->keys, $this->values)
            : array_values($this->values)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function first(): OptionalInterface
    {
        if (empty($this->values)) {
            return Optional::empty();
        }

        reset($this->values);

        return Optional::of(current($this->values));
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->values[key($this->keys)];
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->keys);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return current($this->keys);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return key($this->keys) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->keys);
    }
}
