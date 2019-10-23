<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\HashSet;
use Bdf\Collection\Stream\Accumulator\Accumulators;
use Bdf\Collection\Stream\Collector\Joining;
use Bdf\Collection\Util\Optional;
use PHPUnit\Framework\TestCase;

/**
 * Class DistinctStreamTest
 */
class DistinctStreamTest extends TestCase
{
    /**
     *
     */
    public function test_toArray()
    {
        $stream = new DistinctStream(new ArrayStream([1, 1, 2, 3, 2]), new HashSet());

        $this->assertSame([0 => 1, 2 => 2, 3 => 3], $stream->toArray());
    }

    /**
     *
     */
    public function test_toArray_not_preserve_keys()
    {
        $stream = new DistinctStream(new ArrayStream([1, 1, 2, 3, 2]), new HashSet());

        $this->assertSame([1, 2, 3], $stream->toArray(false));
    }

    /**
     *
     */
    public function test_forEach()
    {
        $stream = new DistinctStream(new ArrayStream([1, 1, 2, 3, 2]), new HashSet());

        $calls = [];

        $stream->forEach(function ($e, $k) use(&$calls) {
            $calls[] = [$e, $k];
        });

        $this->assertEquals([
            [1, 0],
            [2, 2],
            [3, 3]
        ], $calls);
    }

    /**
     *
     */
    public function test_filter()
    {
        $stream = new DistinctStream(new ArrayStream([1, 1, 2, 3, 2]), new HashSet());

        $filterStream = $stream->filter(function ($e) { return $e > 1; });

        $this->assertInstanceOf(FilterStream::class, $filterStream);
        $this->assertEquals([2, 3], array_values($filterStream->toArray()));
    }

    /**
     *
     */
    public function test_map()
    {
        $stream = new DistinctStream(new ArrayStream([1, 1, 2, 3, 2]), new HashSet());

        $mapStream = $stream->map(function ($e) { return $e * 2; });

        $this->assertInstanceOf(MapStream::class, $mapStream);
        $this->assertEquals([2, 4, 6], array_values($mapStream->toArray()));
    }

    /**
     *
     */
    public function test_iterator()
    {
        $stream = new DistinctStream(new ArrayStream([1, 1, 2, 3, 2]), new HashSet());

        $this->assertSame([1, 2, 3], array_values(iterator_to_array($stream)));
    }

    /**
     *
     */
    public function test_first()
    {
        $stream = new DistinctStream(new ArrayStream([1, 1, 2, 3, 2]), new HashSet());

        $this->assertEquals(Optional::of(1), $stream->first());

        $stream = new DistinctStream(new ArrayStream([]), new HashSet());
        $this->assertEquals(Optional::empty(), $stream->first());
    }

    /**
     *
     */
    public function test_distinct()
    {
        $stream = new DistinctStream(new ArrayStream([1, 1, 2, 3, 2]), new HashSet());

        $distinctStream = $stream->distinct(function ($e) { return $e % 2; });

        $this->assertInstanceOf(DistinctStream::class, $distinctStream);
        $this->assertEquals([1, 2], $distinctStream->toArray(false));
    }

    /**
     *
     */
    public function test_sort()
    {
        $stream = new DistinctStream(new ArrayStream([4, 5, 1, 8, 3]), new HashSet());

        $sortStream = $stream->sort();

        $this->assertInstanceOf(SortStream::class, $sortStream);
        $this->assertEquals([1, 3, 4, 5, 8], $sortStream->toArray());
    }

    /**
     *
     */
    public function test_reduce()
    {
        $stream = new DistinctStream(new ArrayStream([4, 5, 1]), new HashSet());

        $this->assertEquals(10, $stream->reduce(Accumulators::sum()));
    }

    /**
     *
     */
    public function test_collection()
    {
        $stream = new DistinctStream(new ArrayStream([4, 5, 1]), new HashSet());

        $this->assertEquals('4:5:1', $stream->collect(new Joining(':')));
    }

    /**
     *
     */
    public function test_concat()
    {
        $stream = new DistinctStream(new ArrayStream([4, 5, 1, 5]), new HashSet());

        $concat = $stream->concat(new ArrayStream([7, 4, 2]), false);
        $this->assertSame([4, 5, 1, 7, 4, 2], $concat->toArray());
    }

    /**
     *
     */
    public function test_flatMap()
    {
        $stream = new DistinctStream(new ArrayStream([[1, 2], [1, 2], [3, 4]]), new HashSet());

        $this->assertSame([1, 2, 3, 4], $stream->flatMap(function ($e) { return $e; })->toArray());
    }

    /**
     *
     */
    public function test_skip()
    {
        $stream = new DistinctStream(new ArrayStream([4, 5, 1, 5]), new HashSet());

        $this->assertInstanceOf(LimitStream::class, $stream->skip(2));
        $this->assertEquals([1], $stream->skip(2)->toArray(false));
        $this->assertEmpty($stream->skip(100)->toArray(false));
    }

    /**
     *
     */
    public function test_limit()
    {
        $stream = new DistinctStream(new ArrayStream([4, 5, 1, 5]), new HashSet());

        $this->assertInstanceOf(LimitStream::class, $stream->limit(2));
        $this->assertEquals([4, 5], $stream->limit(2)->toArray(false));
        $this->assertEquals([5, 1], $stream->limit(2, 1)->toArray(false));
        $this->assertEmpty($stream->limit(2, 10)->toArray(false));
    }

    /**
     *
     */
    public function test_matchAll()
    {
        $stream = new DistinctStream(new ArrayStream([4, 5, 1, 5]), new HashSet());

        $this->assertTrue($stream->matchAll(function ($e) { return $e < 10; }));
        $this->assertFalse($stream->matchAll(function ($e) { return $e % 2 === 0; }));
    }

    /**
     *
     */
    public function test_matchOne()
    {
        $stream = new DistinctStream(new ArrayStream([4, 5, 1, 5]), new HashSet());

        $this->assertTrue($stream->matchOne(function ($e) { return $e % 2 === 0; }));
        $this->assertFalse($stream->matchOne(function ($e) { return $e > 10; }));
    }
}
