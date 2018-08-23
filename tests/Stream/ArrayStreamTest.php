<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\Accumulators;
use Bdf\Collection\Stream\Collector\Joining;
use Bdf\Collection\Util\Optional;
use PHPUnit\Framework\TestCase;

/**
 * Class ArrayStreamTest
 */
class ArrayStreamTest extends TestCase
{
    /**
     *
     */
    public function test_toArray()
    {
        $stream = new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]);

        $this->assertSame([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ], $stream->toArray());

        $this->assertSame(['John', 'Doe' ], $stream->toArray(false));
    }

    /**
     *
     */
    public function test_forEach()
    {
        $stream = new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]);

        $calls = [];

        $stream->forEach(function ($e, $k) use(&$calls) {
            $calls[] = [$e, $k];
        });

        $this->assertEquals([
            ['John', 'firstName'],
            ['Doe', 'lastName'],
        ], $calls);
    }

    /**
     *
     */
    public function test_filter()
    {
        $stream = new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]);

        $filterStream = $stream->filter(function ($e) { return strpos($e, 'J') !== false; });

        $this->assertInstanceOf(FilterStream::class, $filterStream);
        $this->assertEquals(['firstName' => 'John'], $filterStream->toArray());
    }

    /**
     *
     */
    public function test_map()
    {
        $stream = new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]);

        $mapStream = $stream->map(function ($e) { return strtoupper($e); });

        $this->assertInstanceOf(MapStream::class, $mapStream);
        $this->assertEquals([
            'firstName' => 'JOHN',
            'lastName'  => 'DOE'
        ], $mapStream->toArray());
    }

    /**
     *
     */
    public function test_iterator()
    {
        $stream = new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]);

        $this->assertSame([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ], iterator_to_array($stream));
    }

    /**
     *
     */
    public function test_first()
    {
        $stream = new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]);

        $this->assertEquals(Optional::of('John'), $stream->first());
        $this->assertEquals(Optional::empty(), (new ArrayStream([]))->first());
    }

    /**
     *
     */
    public function test_distinct()
    {
        $stream = new ArrayStream([4, 8, 4, 5, 1, 7, 1]);

        $distinctStream = $stream->distinct();

        $this->assertInstanceOf(DistinctStream::class, $distinctStream);
        $this->assertEquals([4, 8, 5, 1, 7], $distinctStream->toArray(false));
    }

    /**
     *
     */
    public function test_distinct_assoc()
    {
        $stream = new ArrayStream([
            'foo' => 42,
            'bar' => 24,
            'baz' => 42
        ]);

        $distinctStream = $stream->distinct();

        $this->assertInstanceOf(DistinctStream::class, $distinctStream);
        $this->assertEquals([
            'foo' => 42,
            'bar' => 24
        ], $distinctStream->toArray());
    }

    /**
     *
     */
    public function test_sort()
    {
        $stream = new ArrayStream([4, 5, 1, 8, 3]);

        $sortStream = $stream->sort();

        $this->assertInstanceOf(SortStream::class, $sortStream);
        $this->assertEquals([1, 3, 4, 5, 8], $sortStream->toArray(false));
    }

    /**
     *
     */
    public function test_sort_with_assoc()
    {
        $stream = new ArrayStream([
            'foo' => 4,
            'bar' => 6,
            'baz' => 2,
        ]);

        $this->assertSame([
            'baz' => 2,
            'foo' => 4,
            'bar' => 6,
        ], $stream->sort(null, true)->toArray());

        $this->assertSame([2, 4, 6], $stream->sort()->toArray());
    }

    /**
     *
     */
    public function test_sort_with_comparator()
    {
        $stream = new ArrayStream([4, 5, 1, 8, 3]);

        $sortStream = $stream->sort(function ($a, $b) {
            return [$a % 2, $a] <=> [$b % 2, $b];
        });

        $this->assertInstanceOf(SortStream::class, $sortStream);
        $this->assertEquals([4, 8, 1, 3, 5], $sortStream->toArray(false));
    }

    /**
     *
     */
    public function test_flatMap_preserve_keys()
    {
        $stream = new ArrayStream([
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
        ]);

        $this->assertSame([
            'foo' => 12,
            'bar' => 42,
            'oof' => 32,
            'baz' => 25
        ], $stream->flatMap(function ($e) { return $e['value']; }, true)->toArray());
    }

    /**
     *
     */
    public function test_flatMap_no_preserve_keys()
    {
        $stream = new ArrayStream([
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
        ]);

        $this->assertSame([12, 42, 32, 25], $stream->flatMap(function ($e) { return $e['value']; })->toArray());
    }

    /**
     *
     */
    public function test_reduce_with_closure()
    {
        $stream = new ArrayStream([4, 5, 1]);

        $this->assertEquals(10, $stream->reduce(function ($a, $b) { return $a + $b; }));
    }

    /**
     *
     */
    public function test_reduce_with_closure_and_initial_value()
    {
        $stream = new ArrayStream([4, 5, 1]);

        $this->assertEquals(15, $stream->reduce(function ($a, $b) { return $a + $b; }, 5));
    }

    /**
     *
     */
    public function test_reduce_with_empty_stream()
    {
        $stream = new ArrayStream([]);
        $called = false;

        $this->assertEquals(5, $stream->reduce(function () use(&$called) { $called = true; }, 5));
        $this->assertFalse($called);
    }

    /**
     *
     */
    public function test_reduce_with_accumulator_functor()
    {
        $stream = new ArrayStream([4, 8, 2]);

        $this->assertEquals(14, $stream->reduce(Accumulators::sum()));
    }

    /**
     *
     */
    public function test_reduce_with_accumulator_functor_and_initial_value()
    {
        $stream = new ArrayStream([4, 8, 2]);

        $this->assertEquals(16, $stream->reduce(Accumulators::sum(), 2));
    }

    /**
     *
     */
    public function test_collector()
    {
        $stream = new ArrayStream([4, 8, 2]);

        $this->assertEquals('4:8:2', $stream->collect(new Joining(':')));
    }

    /**
     *
     */
    public function test_concat()
    {
        $stream = new ArrayStream([4, 8, 2]);

        $concat = $stream->concat(new ArrayStream([7, 4]), false);

        $this->assertInstanceOf(ConcatStream::class, $concat);
        $this->assertEquals([4, 8, 2, 7, 4], $concat->toArray());
    }

    /**
     *
     */
    public function test_matchAll()
    {
        $stream = new ArrayStream([4, 8, 2]);

        $this->assertTrue($stream->matchAll(function ($e) { return $e % 2 === 0; }));
        $this->assertFalse($stream->matchAll(function ($e) { return $e % 4 === 0; }));
    }

    /**
     *
     */
    public function test_matchOne()
    {
        $stream = new ArrayStream([4, 8, 2]);

        $this->assertTrue($stream->matchOne(function ($e) { return $e % 4 === 0; }));
        $this->assertFalse($stream->matchOne(function ($e) { return $e % 2 === 1; }));
    }
}
