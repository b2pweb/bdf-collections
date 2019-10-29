<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\AccumulatorInterface;
use Bdf\Collection\Stream\Collector\CollectorInterface;
use Bdf\Collection\Util\Functor\Consumer\ConsumerInterface;
use Bdf\Collection\Util\Functor\Predicate\PredicateInterface;
use Bdf\Collection\Util\Functor\Transformer\TransformerInterface;
use Bdf\Collection\Util\OptionalInterface;
use Iterator;

/**
 * Stream apply operations on each elements of a Collection
 *
 * A stream instance can only be used once. It has two types of methods :
 * - Transformation methods which return a new stream for applying transformations on elements. Any methods can be called after a transformation method
 * - Terminal methods which iterate over the stream and "close" the stream. After calling a terminal method, no more methods can be called
 *
 * The transformations will be applied only when a termination method is called. So a stream should be used like :
 * - Call one or more transformations
 * - Finish processing with a terminal method
 *
 * <code>
 * $collection->stream() // Create the stream from the collection
 *     ->map(...) // Apply transformations
 *     ->filter(...)
 *     ->forEach(...) // Terminate the stream
 * ;
 * </code>
 */
interface StreamInterface extends Iterator
{
    /**
     * Apply $transformer to each values of the stream
     *
     * <code>
     * $stream = new ArrayStream([1, 2, 3]);
     * $stream
     *     ->map(function ($element, $key) { return $element * 2; })
     *     ->toArray() // [2, 4, 6]
     * ;
     * </code>
     *
     * @param callable $transformer The element transformer.
     *     Should take the element as first parameter an return the transformed element
     *     The transformer may have (if relevant) the key as second parameter
     *
     * @return StreamInterface
     *
     * @see TransformerInterface
     */
    public function map(callable $transformer): StreamInterface;

    /**
     * Apply $function to each values of the stream for generates keys
     *
     * The return type of the function is not checked, and duplicate keys, or illegal array offset may be generated.
     * In such cases, toArray(), or other collector methods may have undefined behavior (others stream methods can be used safely).
     * For remove elements with same result of the function, use distinct(), or IndexingBy collector.
     *
     * <code>
     * $stream = new ArrayStream([1, 2, 3]);
     * $stream
     *     ->map(function ($element, $key) { return $element * 2; })
     *     ->toArray() // [2 => 1, 4 => 2, 6 => 3]
     * ;
     *
     * // Apply transformation to the key (snake_case to PascalCase)
     * $stream = new ArrayStream(['first_name' => 'John', 'last_name' => 'Doe']);
     * $stream
     *     ->mapKey(function ($e, $key) {
     *         return Streams::wrap(explode('_', $key))->map(function ($k) { return ucfirst($k); })->collect(new Joining());
     *     })
     *     ->toArray() // ['FirstName' => 'John', 'LastName' => 'Doe']
     * ;
     * </code>
     *
     * @param callable $function The key generator.
     *     Should take the element as first parameter, the key as second parameter, and return the new key
     *
     * @return StreamInterface
     *
     * @see TransformerInterface
     */
    public function mapKey(callable $function): StreamInterface;

    /**
     * Filter the stream
     *
     * <code>
     * $stream = new ArrayStream([1, 2, 3]);
     * $stream
     *     ->filter(function ($element, $key) { return $element % 2 !== 0; })
     *     ->toArray() // [1, 3]
     * ;
     * </code>
     *
     * @param callable $predicate The predicate function.
     *     Take the element as first parameter and should return a boolean (true for keeping element, or false for skipping)
     *     May take the key as second parameter (if relevant)
     *
     * @return StreamInterface
     *
     * @see PredicateInterface
     */
    public function filter(callable $predicate): StreamInterface;

    /**
     * Filter stream elements to get only distinct elements
     * Two elements are considered as equals when there hash are equals : `$hashFunction($e1) === $hashFunction($e2)`
     *
     * If the hash function is not provided, elements will be compared using :
     * - If it's an Hashable object, the Hashable::hash() method
     * - In other case, compare with value AND type
     *
     * By default, int(123) and string('123') are not considered as equal, and will be keep into the distinct stream
     *
     * <code>
     * $stream = new ArrayStream([4, 8, 1, 4, 1]);
     * $stream->distinct(); // [4, 8, 1]
     *
     * $stream = new ArrayStream([[1, 2],  [2, 3], [2, 1]]);
     * $stream->distinct(function ($e) { sort($e); return json_encode($e); }); // [[1, 2], [2, 3]]
     * </code>
     *
     * @param callable $hashFunction The hash function. Take as parameter the element, and return the hash value as string
     *
     * @return StreamInterface
     */
    public function distinct(callable $hashFunction = null): StreamInterface;

    /**
     * Order stream elements
     *
     * <code>
     * $stream = new ArrayStream([8, 4, 5, 3]);
     * $stream->sort()->toArray(); // [3, 4, 5, 8]
     *
     * $stream
     *     ->sort(function ($a, $b) { return ([$a % 2, $a] <=> [$b % 2, $b]); })
     *     ->toArray() // [4, 8, 3, 5]
     * ;
     *
     * // Sort keeping keys
     * $stream = new ArrayStream([
     *     'foo' => 3,
     *     'bar' => 42,
     *     'baz' => 9
     * ]);
     *
     * $stream->sort(null, true)->toArray();
     * // [ 'foo' => 3,
     * //   'baz' => 9,
     * //   'bar' => 42 ]
     * </code>
     *
     * /!\ Unlike other transformations, the elements are fetched before execution of the terminal method
     *
     * Ex :
     * <code>
     * $stream = new ArrayStream([1, 2, 3]);
     *
     * // Display : map(1) forEach(1) map(2) forEach(2) map(3) forEach(3)
     * $stream
     *     ->map(function ($e) { echo "map($e) "; return $e; })
     *     ->forEach(function ($e) { echo "forEach($e) "; })
     * ;
     *
     * // Display : map(1) map(2) map(3) forEach(1) forEach(2) forEach(3)
     * $stream
     *     ->map(function ($e) { echo "map($e) "; return $e; })
     *     ->sort() // Sort fetch the map stream
     *     ->forEach(function ($e) { echo "forEach($e) "; })
     * ;
     * </code>
     *
     * @param callable $comparator The comparator, or null to use default comparison.
     *                             Take the two values to compare as parameters and should return an integer :
     *                             - $comparator($a, $b) < 0 => $a < $b
     *                             - $comparator($a, $b) == 0 => $a == $b
     *                             - $comparator($a, $b) > 0 => $a > $b
     *
     * @param boolean $preserveKeys If true, the keys will be kept, else an the values will be indexed by an increment integer
     *
     * @return StreamInterface
     */
    public function sort(callable $comparator = null, bool $preserveKeys = false): StreamInterface;

    /**
     * Concatenate a new stream after the current stream
     * The current stream will be the first executed stream, and the concatenated one will be executed after
     *
     * /!\ The current stream must not be an infinite stream
     *
     * <code>
     * $stream = new ArrayStream([1, 2, 3]);
     * $stream
     *     ->concat(new ArrayStream([4, 5, 6]), false)
     *     ->toArray() // [1, 2, 3, 4, 5, 6]
     * ;
     * </code>
     *
     * @param StreamInterface $stream The stream to concat
     * @param bool $preserveKeys Preserve the stream keys, or use integer increment index
     *
     * @return StreamInterface
     */
    public function concat(StreamInterface $stream, bool $preserveKeys = true): StreamInterface;

    /**
     * Create a stream resulting of concatenation of each elements content extracted by $transformer
     * This method reduce by one the depth of multidimensional stream
     *
     * Example:
     * <code>
     * $stream = new ArrayStream([
     *     ['values' => [1, 2]],
     *     ['values' => 3],
     *     ['values' => new ArrayStream([4, 5])],
     * ]);
     *
     * $stream->flatMap(function ($e) { return $e['values']; })->toArray(); // [1, 2, 3, 4, 5]
     * </code>
     *
     * (i) Each transformed elements will be transformed to a Stream using Streams::wrap()
     *     Empty array and null will be transformed to an EmptyStream, array to an array stream, etc...
     *     For ensure that no transformation is applied, the transformer should return a StreamInterface
     *
     * This method is equivalent with :
     * <code>
     * $stream
     *     ->map($transformer)
     *     ->map(function ($e) { return Streams::wrap($e); })
     *     ->reduce(
     *         function (StreamInterface $a, StreamInterface $b) { return $a->concat($b); },
     *         EmptyStream::instance()
     *     )
     * ;
     * </code>
     *
     * @param callable|null $transformer The element transformer
     *     Should take the element as first parameter an return the transformed element
     *     The transformer may have (if relevant) the key as second parameter
     *
     * @param bool $preserveKeys Preserve the sub-streams keys, or use integer increment index
     *
     * @return StreamInterface
     *
     * @see TransformerInterface
     * @see Streams::wrap() Used to transform each transformed elements to a Stream
     */
    public function flatMap(callable $transformer, bool $preserveKeys = false): StreamInterface;

    /**
     * Skip the $count first elements of the stream.
     * Give a count higher than the number of elements of the stream will results of an empty stream.
     *
     * Example:
     * <code>
     * $stream = new ArrayStream([1, 2, 3, 4]);
     * $stream->skip(2)->toArray(); // [3, 4]
     * </code>
     *
     * @param int $count Number of elements to skip. Must be a positive number.
     *
     * @return StreamInterface
     *
     * @see StreamInterface::limit() For limit the number of stream's elements
     */
    public function skip(int $count): StreamInterface;

    /**
     * Limit the number of elements of the stream.
     * Stop the stream when it reach $count elements.
     *
     * Example:
     * <code>
     * $stream = new ArrayStream([1, 2, 3, 4]);
     * $stream->limit(2)->toArray(); // [1, 2]
     * $stream->limit(2, 1)->toArray(); // [2, 3]
     * </code>
     *
     * @param int $count The maximum number elements
     * @param int $offset Number of elements to skip at start of the stream
     *
     * @return StreamInterface
     *
     * @see StreamInterface::skip() For skip firsts elements
     */
    public function limit(int $count, int $offset = 0): StreamInterface;

    /**
     * Iterate over all stream elements.
     * This method is a terminal method : the stream must not be used after
     *
     * <code>
     * $stream = new ArrayStream([1, 2, 3]);
     * $stream->forEach(function ($element, $key) {
     *     $element->doSomething();
     * });
     * </code>
     *
     * @param callable $consumer
     *
     * @return void
     *
     * @see ConsumerInterface
     */
    public function forEach(callable $consumer): void;

    /**
     * Aggregate the stream to an array
     * This method is a terminal method : the stream must not be used after
     *
     * <code>
     * $stream = new ArrayStream([
     *     'foo' => 'bar',
     *     'value' => 42
     * ]);
     *
     * $stream->toArray() === ['foo' => 'bar', 'value' => 42];
     * $stream->toArray(false) === ['bar', 42];
     * ];
     * </code>
     *
     * @param bool $preserveKeys True to preserve the keys of the stream, or false for reindex with increment integer.
     *     This parameter must be set to false when stream contains complex keys (not integer or string)
     *
     * @return array
     */
    public function toArray(bool $preserveKeys = true): array;

    /**
     * Get the first element of the stream
     * The element will be wrapped into an Optional for handle empty stream
     *
     * <code>
     * $stream = new ArrayStream([1, 2, 3]);
     * $stream->first(); // Optional(1);
     * $stream->filter(function () { return false; })->first(); // Empty Optional
     * </code>
     *
     * @return OptionalInterface
     */
    public function first(): OptionalInterface;

    /**
     * Reduce all elements of the stream into a single value
     * This method is a terminal method : the stream must not be used after
     *
     * <code>
     * $stream = new ArrayStream([1, 2, 3]);
     * $stream->reduce(function ($carry, $item) { return (int) $carry + $item; }); // 6
     * $stream->reduce(Accumulators::sum()); // Same as above, but with a functor
     * </code>
     *
     * @param callable|AccumulatorInterface $accumulator The accumulator.
     *     When a callback is given : takes the reduced value as first parameter and the item to accumulate as second parameter. The function must return the reduced value
     *     When an AccumulatorInterface is given as only parameter, the initial value will be $accumulator->initial()
     * @param mixed $initial The initial value
     *
     * @return mixed The reduced value, or $initial if the stream is empty
     *
     * @see AccumulatorInterface For functor implementation
     */
    public function reduce(callable $accumulator, $initial = null);

    /**
     * Collect all elements into a single value
     * This method is a terminal method : the stream must not be used after
     *
     * The behavior of this method is very similar to reduce() but with some differences :
     * - The collector is not stateless
     * - It has a finalisation method whereas reduce perform aggregation on each iterations
     *
     * @param CollectorInterface $collector The collector
     *
     * @return mixed
     */
    public function collect(CollectorInterface $collector);

    /**
     * Check if all elements of the stream match with the predicate
     * This method is a terminal method : the stream must not be used after
     *
     * Note: An empty stream will always return true
     *
     * /!\ One infinite stream, this method may cause an infinite loop
     *
     * <code>
     * $stream = new ArrayStream([1, 2, 3]);
     *
     * $stream->allMatch(function ($e) { return $e < 5; }); // true
     * $stream->allMatch(function ($e) { return $e % 2 === 0; }); // false
     * </code>
     *
     * @param callable $predicate The predicate function.
     *     Take the element as first parameter and should return a boolean (true if matching)
     *     May take the key as second parameter (if relevant)
     *
     * @return bool
     *
     * @see PredicateInterface
     */
    public function matchAll(callable $predicate): bool;

    /**
     * Check if at least one element of the stream match with the predicate
     * This method is a terminal method : the stream must not be used after
     *
     * /!\ One infinite stream, this method may cause an infinite loop
     *
     * <code>
     * $stream = new ArrayStream([1, 2, 3]);
     *
     * $stream->allMatch(function ($e) { return $e % 2 === 0; }); // true : 2 % 2 === 0
     * $stream->allMatch(function ($e) { return $e > 5; }); // false
     * </code>
     *
     * @param callable $predicate The predicate function.
     *     Take the element as first parameter and should return a boolean (true if matching)
     *     May take the key as second parameter (if relevant)
     *
     * @return bool
     *
     * @see PredicateInterface
     */
    public function matchOne(callable $predicate): bool;
}
