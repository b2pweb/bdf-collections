<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\Accumulators;
use Bdf\Collection\Stream\Collector\Joining;
use PHPUnit\Framework\TestCase;

/**
 * Class IteratorStreamTest
 */
class IteratorStreamTest extends TestCase
{
    /**
     *
     */
    public function test_toArray()
    {
        $stream = new IteratorStream(new \ArrayIterator([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]));

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
        $stream = new IteratorStream(new \ArrayIterator([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]));

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
        $stream = new IteratorStream(new \ArrayIterator([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]));

        $filterStream = $stream->filter(function ($e) { return strpos($e, 'J') !== false; });

        $this->assertInstanceOf(FilterStream::class, $filterStream);
        $this->assertEquals(['firstName' => 'John'], $filterStream->toArray());
    }

    /**
     *
     */
    public function test_map()
    {
        $stream = new IteratorStream(new \ArrayIterator([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]));

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
        $stream = new IteratorStream(new \ArrayIterator([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]));

        $this->assertSame([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ], iterator_to_array($stream));
    }

    /**
     *
     */
    public function test_distinct()
    {
        $stream = new IteratorStream(new \ArrayIterator([4, 8, 4, 5, 1, 7, 1]));

        $distinctStream = $stream->distinct();

        $this->assertInstanceOf(DistinctStream::class, $distinctStream);
        $this->assertEquals([4, 8, 5, 1, 7], array_values($distinctStream->toArray()));
    }

    /**
     *
     */
    public function test_sort()
    {
        $stream = new IteratorStream(new \ArrayIterator([4, 5, 1, 8, 3]));

        $sortStream = $stream->sort();

        $this->assertInstanceOf(SortStream::class, $sortStream);
        $this->assertEquals([1, 3, 4, 5, 8], $sortStream->toArray());
    }

    /**
     *
     */
    public function test_reduce()
    {
        $stream = new IteratorStream(new \ArrayIterator([4, 5, 1]));

        $this->assertEquals(10, $stream->reduce(Accumulators::sum()));
    }

    /**
     *
     */
    public function test_collect()
    {
        $stream = new IteratorStream(new \ArrayIterator([4, 5, 1]));

        $this->assertEquals('4:5:1', $stream->collect(new Joining(':')));
    }

    /**
     *
     */
    public function test_concat()
    {
        $stream = new IteratorStream(new \ArrayIterator([4, 5, 1]));

        $this->assertSame([4, 5, 1, 2, 7], $stream->concat(new ArrayStream([2, 7]), false)->toArray());
    }

    /**
     *
     */
    public function test_matchAll()
    {
        $stream = new IteratorStream(new \ArrayIterator([4, 5, 1]));

        $this->assertTrue($stream->matchAll(function ($e) { return $e < 10; }));
        $this->assertFalse($stream->matchAll(function ($e) { return $e % 2 === 0; }));
    }

    /**
     *
     */
    public function test_matchOne()
    {
        $stream = new IteratorStream(new \ArrayIterator([4, 5, 1]));

        $this->assertTrue($stream->matchOne(function ($e) { return $e % 2 === 0; }));
        $this->assertFalse($stream->matchOne(function ($e) { return $e > 10; }));
    }
}
