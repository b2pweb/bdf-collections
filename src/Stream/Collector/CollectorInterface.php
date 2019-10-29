<?php

namespace Bdf\Collection\Stream\Collector;

use Bdf\Collection\Stream\StreamInterface;

/**
 * Collect the stream elements and accumulate into a single object
 *
 * Its behavior is slightly different from AccumulatorInterface :
 * - It's not stateless
 * - It has a finalize method which perform the final aggregation (Accumulator perform the aggregation step by step)
 * - It handles key
 *
 * <code>
 * class HashCollector implements CollectorInterface {
 *     private $context;
 *
 *     public function __construct($algo) {
 *         $this->context = hash_init($algo);
 *     }
 *
 *     public function aggregate($element, $key = null) {
 *         hash_update($this->context, $element);
 *     }
 *
 *     public function finalize() {
 *         return hash_final($this->context);
 *     }
 * }
 *
 * $stream = new ArrayStream(['Hello', 'World', '!']);
 * $stream->collect(new HashCollector('md5')); // Perform MD5 hash on each elements
 * </code>
 *
 * @see StreamInterface::collect()
 */
interface CollectorInterface
{
    /**
     * Aggregate the element to collect
     * The collector state will change by this call
     *
     * @param mixed $element Element to aggregate
     * @param mixed $key The element key, if applicable
     *
     * @return void
     */
    public function aggregate($element, $key = null): void;

    /**
     * Finalize the aggregation, and prepare the return value
     *
     * @return mixed
     */
    public function finalize();
}
