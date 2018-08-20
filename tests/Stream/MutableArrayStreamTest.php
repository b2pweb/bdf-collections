<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\Accumulators;
use Bdf\Collection\Stream\Collector\Joining;
use Bdf\Collection\Util\Optional;
use PHPUnit\Framework\TestCase;

/**
 * Class MutableArrayStreamTest
 */
class MutableArrayStreamTest extends TestCase
{
    /**
     *
     */
    public function test_toArray()
    {
        $stream = new MutableArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]);

        $this->assertSame([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ], $stream->toArray());
        $this->assertSame(['John', 'Doe'], $stream->toArray(false));
    }

    /**
     *
     */
    public function test_forEach()
    {
        $stream = new MutableArrayStream([
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
        $stream = new MutableArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]);

        $this->assertSame($stream, $stream->filter(function ($e) { return strpos($e, 'J') !== false; }));
        $this->assertEquals(['firstName' => 'John'], $stream->toArray());
    }

    /**
     *
     */
    public function test_map()
    {
        $stream = new MutableArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]);

        $this->assertSame($stream, $stream->map(function ($e) { return strtoupper($e); }));
        $this->assertEquals([
            'firstName' => 'JOHN',
            'lastName'  => 'DOE'
        ], $stream->toArray());
    }

    /**
     *
     */
    public function test_iterator()
    {
        $stream = new MutableArrayStream([
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
        $stream = new MutableArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]);

        $this->assertEquals(Optional::of('John'), $stream->first());
        $this->assertEquals(Optional::empty(), (new MutableArrayStream([]))->first());
    }

    /**
     *
     */
    public function test_distinct()
    {
        $stream = new MutableArrayStream([4, 8, 4, 5, 1, 7, 1]);

        $this->assertSame($stream, $stream->distinct());
        $this->assertEquals([4, 8, 5, 1, 7], array_values($stream->toArray()));
    }

    /**
     *
     */
    public function test_sort_assoc()
    {
        $stream = new MutableArrayStream([
            'foo' => 4,
            'bar' => 5,
            'baz' => 1,
            'oof' => 8,
            'rab' => 3
        ]);

        $this->assertSame($stream, $stream->sort(null, true));
        $this->assertSame(['baz' => 1, 'rab' => 3, 'foo' => 4, 'bar' => 5, 'oof' => 8], $stream->toArray());
    }

    /**
     *
     */
    public function test_sort()
    {
        $stream = new MutableArrayStream([4, 5, 1, 8, 3]);

        $this->assertSame($stream, $stream->sort());
        $this->assertSame([1, 3, 4, 5, 8], $stream->toArray());
    }

    /**
     *
     */
    public function test_sort_with_comparator()
    {
        $stream = new MutableArrayStream([4, 5, 1, 8, 3]);

        $this->assertSame($stream, $stream->sort(function ($a, $b) {
            return [$a % 2, $a] <=> [$b % 2, $b];
        }, false));

        $this->assertEquals([4, 8, 1, 3, 5], $stream->toArray());
    }

    /**
     *
     */
    public function test_sort_assoc_with_comparator()
    {
        $stream = new MutableArrayStream([
            'foo' => 4,
            'bar' => 5,
            'baz' => 1,
            'oof' => 8,
            'rab' => 3
        ]);

        $this->assertSame($stream, $stream->sort(function ($a, $b) {
            return [$a % 2, $a] <=> [$b % 2, $b];
        }, true));
        $this->assertSame(['foo' => 4, 'oof' => 8, 'baz' => 1, 'rab' => 3, 'bar' => 5], $stream->toArray());
    }

    /**
     *
     */
    public function test_reduce_with_closure()
    {
        $stream = new MutableArrayStream([4, 5, 1]);

        $this->assertEquals(10, $stream->reduce(function ($a, $b) { return $a + $b; }));
    }

    /**
     *
     */
    public function test_reduce_with_closure_and_initial_value()
    {
        $stream = new MutableArrayStream([4, 5, 1]);

        $this->assertEquals(15, $stream->reduce(function ($a, $b) { return $a + $b; }, 5));
    }

    /**
     *
     */
    public function test_reduce_with_empty_stream()
    {
        $stream = new MutableArrayStream([]);
        $called = false;

        $this->assertEquals(5, $stream->reduce(function () use(&$called) { $called = true; }, 5));
        $this->assertFalse($called);
    }

    /**
     *
     */
    public function test_reduce_with_accumulator_functor()
    {
        $stream = new MutableArrayStream([4, 8, 2]);

        $this->assertEquals(14, $stream->reduce(Accumulators::sum()));
    }

    /**
     *
     */
    public function test_reduce_with_accumulator_functor_and_initial_value()
    {
        $stream = new MutableArrayStream([4, 8, 2]);

        $this->assertEquals(16, $stream->reduce(Accumulators::sum(), 2));
    }

    /**
     *
     */
    public function test_collector()
    {
        $stream = new MutableArrayStream([4, 8, 2]);

        $this->assertEquals('4:8:2', $stream->collect(new Joining(':')));
    }
}
