<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\Accumulators;
use Bdf\Collection\Stream\Collector\Joining;
use Bdf\Collection\Util\Optional;
use PHPUnit\Framework\TestCase;

/**
 * Class LimitStreamTest
 */
class LimitStreamTest extends TestCase
{
    /**
     *
     */
    public function test_toArray()
    {
        $stream = new LimitStream(new ArrayStream([1, 2, 3, 4]), 1, 2);

        $this->assertSame([
            1 => 2,
            2 => 3,
        ], $stream->toArray());

        $this->assertSame([2, 3], $stream->toArray(false));
    }

    /**
     *
     */
    public function test_forEach()
    {
        $stream = new LimitStream(new ArrayStream([1, 2, 3, 4]), 1, 2);

        $calls = [];

        $stream->forEach(function ($e, $k) use(&$calls) {
            $calls[] = [$e, $k];
        });

        $this->assertEquals([
            [2, 1],
            [3, 2],
        ], $calls);
    }

    /**
     *
     */
    public function test_filter()
    {
        $stream = new LimitStream(new ArrayStream([1, 2, 3, 4]), 1, 2);

        $filterStream = $stream->filter(function ($e) { return $e % 2 === 0; });

        $this->assertInstanceOf(FilterStream::class, $filterStream);
        $this->assertEquals([1 => 2], $filterStream->toArray());
    }

    /**
     *
     */
    public function test_map()
    {
        $stream = new LimitStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), 1, 1);

        $mapStream = $stream->map(function ($e) { return strtoupper($e); });

        $this->assertInstanceOf(MapStream::class, $mapStream);
        $this->assertEquals([
            'lastName'  => 'DOE'
        ], $mapStream->toArray());
    }

    /**
     *
     */
    public function test_iterator()
    {
        $stream = new LimitStream(new ArrayStream([1, 2, 3, 4]), 1, 2);

        $this->assertSame([
            1 => 2,
            2 => 3,
        ], iterator_to_array($stream));
    }

    /**
     *
     */
    public function test_first()
    {
        $stream = new LimitStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), 1);

        $this->assertEquals(Optional::of('Doe'), $stream->first());

        $stream = new LimitStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), 10);
        $this->assertEquals(Optional::empty(), $stream->first());
    }

    /**
     *
     */
    public function test_distinct()
    {
        $stream = new LimitStream(new ArrayStream([4, 8, 4, 5, 1, 7, 1]), 2);

        $distinctStream = $stream->distinct();

        $this->assertInstanceOf(DistinctStream::class, $distinctStream);
        $this->assertEquals([4, 5, 1, 7], $distinctStream->toArray(false));
    }

    /**
     *
     */
    public function test_distinct_assoc()
    {
        $stream = new LimitStream(new ArrayStream([
            'bar' => 24,
            'foo' => 42,
            'baz' => 42,
            'aaa' => 14,
            'bbb' => 15,
        ]), 1, 3);

        $distinctStream = $stream->distinct();

        $this->assertInstanceOf(DistinctStream::class, $distinctStream);
        $this->assertEquals([
            'foo' => 42,
            'aaa' => 14,
        ], $distinctStream->toArray());
    }

    /**
     *
     */
    public function test_sort()
    {
        $stream = new LimitStream(new ArrayStream([4, 5, 1, 8, 3]), 1, 3);

        $sortStream = $stream->sort();

        $this->assertInstanceOf(SortStream::class, $sortStream);
        $this->assertEquals([1, 5, 8], $sortStream->toArray(false));
    }

    /**
     *
     */
    public function test_sort_with_assoc()
    {
        $stream = new LimitStream(new ArrayStream([
            'foo' => 4,
            'bar' => 6,
            'baz' => 2,
            'aaa' => 5,
            'bbb' => 1,
        ]), 1, 3);

        $this->assertSame([
            'baz' => 2,
            'aaa' => 5,
            'bar' => 6,
        ], $stream->sort(null, true)->toArray());

        $this->assertSame([2, 5, 6], $stream->sort()->toArray());
    }

    /**
     *
     */
    public function test_sort_with_comparator()
    {
        $stream = new LimitStream(new ArrayStream([4, 5, 1, 8, 3]), 1, 3);

        $sortStream = $stream->sort(function ($a, $b) {
            return [$a % 2, $a] <=> [$b % 2, $b];
        });

        $this->assertInstanceOf(SortStream::class, $sortStream);
        $this->assertEquals([8, 1, 5], $sortStream->toArray(false));
    }

    /**
     *
     */
    public function test_flatMap_preserve_keys()
    {
        $stream = new LimitStream(new ArrayStream([
            [
                'value' => [
                    'foo' => 12,
                    'bar' => 42,
                ]
            ],
            [
                'value' => [
                    'oof' => 32,
                    'baz' => 25,
                ]
            ],
        ]), 1, 1);

        $this->assertSame([
            'oof' => 32,
            'baz' => 25
        ], $stream->flatMap(function ($e) { return $e['value']; }, true)->toArray());
    }

    /**
     *
     */
    public function test_flatMap_no_preserve_keys()
    {
        $stream = new LimitStream(new ArrayStream([
            [
                'value' => [
                    'foo' => 12,
                    'bar' => 42,
                ]
            ],
            [
                'value' => [
                    'oof' => 32,
                    'baz' => 25,
                ]
            ],
        ]), 1, 1);

        $this->assertSame([32, 25], $stream->flatMap(function ($e) { return $e['value']; })->toArray());
    }

    /**
     *
     */
    public function test_reduce_with_closure()
    {
        $stream = new LimitStream(new ArrayStream([4, 5, 1]), 1, 2);

        $this->assertEquals(6, $stream->reduce(function ($a, $b) { return $a + $b; }));
    }

    /**
     *
     */
    public function test_reduce_with_closure_and_initial_value()
    {
        $stream = new LimitStream(new ArrayStream([4, 5, 1]), 1, 2);

        $this->assertEquals(11, $stream->reduce(function ($a, $b) { return $a + $b; }, 5));
    }

    /**
     *
     */
    public function test_reduce_with_empty_stream()
    {
        $stream = new LimitStream(new ArrayStream([]), 1, 1);
        $called = false;

        $this->assertEquals(5, $stream->reduce(function () use(&$called) { $called = true; }, 5));
        $this->assertFalse($called);
    }

    /**
     *
     */
    public function test_reduce_with_accumulator_functor()
    {
        $stream = new LimitStream(new ArrayStream([4, 8, 2]), 1, 2);

        $this->assertEquals(10, $stream->reduce(Accumulators::sum()));
    }

    /**
     *
     */
    public function test_reduce_with_accumulator_functor_and_initial_value()
    {
        $stream = new LimitStream(new ArrayStream([4, 8, 2]), 1, 2);

        $this->assertEquals(12, $stream->reduce(Accumulators::sum(), 2));
    }

    /**
     *
     */
    public function test_collector()
    {
        $stream = new LimitStream(new ArrayStream([4, 8, 2]), 1, 2);

        $this->assertEquals('8:2', $stream->collect(new Joining(':')));
    }

    /**
     *
     */
    public function test_concat()
    {
        $stream = new LimitStream(new ArrayStream([4, 8, 2]), 1, 2);

        $concat = $stream->concat(new ArrayStream([7, 4]), false);

        $this->assertInstanceOf(ConcatStream::class, $concat);
        $this->assertEquals([8, 2, 7, 4], $concat->toArray());
    }

    /**
     *
     */
    public function test_matchAll()
    {
        $stream = new LimitStream(new ArrayStream([4, 8, 2]), 1, 2);

        $this->assertTrue($stream->matchAll(function ($e) { return $e % 2 === 0; }));
        $this->assertFalse($stream->matchAll(function ($e) { return $e % 4 === 0; }));
    }

    /**
     *
     */
    public function test_matchOne()
    {
        $stream = new LimitStream(new ArrayStream([4, 8, 2]), 1, 2);

        $this->assertTrue($stream->matchOne(function ($e) { return $e % 4 === 0; }));
        $this->assertFalse($stream->matchOne(function ($e) { return $e % 2 === 1; }));
    }

    /**
     *
     */
    public function test_skip()
    {
        $stream = new LimitStream(new ArrayStream([4, 8, 2]), 1, 2);

        $skip = $stream->skip(1);

        $this->assertInstanceOf(LimitStream::class, $skip);
        $this->assertEquals([2], $skip->toArray(false));
        $this->assertEmpty($stream->skip(100)->toArray());
    }

    /**
     *
     */
    public function test_limit()
    {
        $stream = new LimitStream(new ArrayStream([4, 8, 2]), 1, 2);

        $limit = $stream->limit(2);

        $this->assertInstanceOf(LimitStream::class, $limit);
        $this->assertEquals([8, 2], $limit->toArray(false));
        $this->assertEquals([2], $stream->limit(2, 1)->toArray(false));

        $this->assertEmpty($stream->limit(1, 100)->toArray());
        $this->assertEmpty($stream->limit(0)->toArray());
    }
}
