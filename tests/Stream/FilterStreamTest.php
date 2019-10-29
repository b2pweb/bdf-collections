<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\Accumulators;
use Bdf\Collection\Stream\Collector\Joining;
use Bdf\Collection\Util\Optional;
use PHPUnit\Framework\TestCase;

/**
 * Class FilterStreamTest
 */
class FilterStreamTest extends TestCase
{
    /**
     *
     */
    public function test_toArray()
    {
        $stream = new FilterStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e) { return strpos($e, 'J') !== false; });

        $this->assertSame(['firstName' => 'John'], $stream->toArray());
        $this->assertSame(['John'], $stream->toArray(false));
    }

    /**
     *
     */
    public function test_forEach()
    {
        $stream = new FilterStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e) { return strpos($e, 'J') !== false; });

        $calls = [];

        $stream->forEach(function ($e, $k) use(&$calls) {
            $calls[] = [$e, $k];
        });

        $this->assertEquals([
            ['John', 'firstName'],
        ], $calls);
    }

    /**
     *
     */
    public function test_filter()
    {
        $stream = new FilterStream(new ArrayStream([1, 2, 3]), function ($e) { return $e % 2 === 1; });

        $filterStream = $stream->filter(function ($e) { return $e > 1; });

        $this->assertInstanceOf(FilterStream::class, $filterStream);
        $this->assertEquals([2 => 3], $filterStream->toArray());
    }

    /**
     *
     */
    public function test_map()
    {
        $stream = new FilterStream(new ArrayStream([1, 2, 3]), function ($e) { return $e % 2 === 1; });

        $mapStream = $stream->map(function ($e) { return $e * 2; });

        $this->assertInstanceOf(MapStream::class, $mapStream);
        $this->assertEquals([
            0 => 2,
            2 => 6
        ], $mapStream->toArray());
    }

    /**
     *
     */
    public function test_mapKey()
    {
        $stream = new FilterStream(new ArrayStream([1, 2, 3]), function ($e) { return $e % 2 === 1; });

        $mapStream = $stream->mapKey(function ($e) { return $e * 2; });

        $this->assertInstanceOf(MapKeyStream::class, $mapStream);
        $this->assertEquals([
            2 => 1,
            6 => 3
        ], $mapStream->toArray());
    }

    /**
     *
     */
    public function test_iterator()
    {
        $stream = new FilterStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e) { return strpos($e, 'J') !== false; });

        $this->assertSame([
            'firstName' => 'John',
        ], iterator_to_array($stream));
    }

    /**
     *
     */
    public function test_first()
    {
        $stream = new FilterStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e) { return strpos($e, 'J') !== false; });

        $this->assertEquals(Optional::of('John'), $stream->first());

        $stream = new FilterStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e) { return false; });
        $this->assertEquals(Optional::empty(), $stream->first());
    }

    /**
     *
     */
    public function test_distinct()
    {
        $stream = new FilterStream(new ArrayStream([4, 8, 4, 5, 1, 7, 1]), function ($e) { return $e < 6; });

        $distinctStream = $stream->distinct();

        $this->assertInstanceOf(DistinctStream::class, $distinctStream);
        $this->assertEquals([4, 5, 1], array_values($distinctStream->toArray()));
    }

    /**
     *
     */
    public function test_sort()
    {
        $stream = new FilterStream(new ArrayStream([4, 5, 1, 8, 3]), function ($e) { return $e < 6; });

        $sortStream = $stream->sort();

        $this->assertInstanceOf(SortStream::class, $sortStream);
        $this->assertEquals([1, 3, 4, 5], $sortStream->toArray());
    }

    /**
     *
     */
    public function test_reduce()
    {
        $stream = new FilterStream(new ArrayStream([4, 5, 1, 8, 3]), function ($e) { return $e < 6; });

        $this->assertEquals(13, $stream->reduce(Accumulators::sum()));
    }

    /**
     *
     */
    public function test_collect()
    {
        $stream = new FilterStream(new ArrayStream([4, 5, 1, 8, 3]), function ($e) { return $e < 6; });

        $this->assertEquals('4:5:1:3', $stream->collect(new Joining(':')));
    }

    /**
     *
     */
    public function test_concat()
    {
        $stream = new FilterStream(new ArrayStream([4, 5, 1, 8, 3]), function ($e) { return $e < 6; });

        $this->assertSame([4, 5, 1, 3, 4, 3], $stream->concat(new ArrayStream([4, 3]), false)->toArray());
    }

    /**
     *
     */
    public function test_flatMap()
    {
        $stream = new FilterStream(new ArrayStream([[1, 2], [3], [4, 5]]), function ($e) { return count($e) > 1; });

        $this->assertSame([1, 2, 4, 5], $stream->flatMap(function ($e) { return $e; })->toArray());
    }

    /**
     *
     */
    public function test_skip()
    {
        $stream = new FilterStream(new ArrayStream([4, 5, 1, 8, 3]), function ($e) { return $e < 6; });

        $this->assertInstanceOf(LimitStream::class, $stream->skip(2));
        $this->assertEquals([1, 3], $stream->skip(2)->toArray(false));
        $this->assertEmpty($stream->skip(20)->toArray(false));
    }

    /**
     *
     */
    public function test_limit()
    {
        $stream = new FilterStream(new ArrayStream([4, 5, 1, 8, 3]), function ($e) { return $e < 6; });

        $this->assertInstanceOf(LimitStream::class, $stream->limit(2));
        $this->assertEquals([4, 5], $stream->limit(2)->toArray(false));
        $this->assertEquals([5, 1], $stream->limit(2, 1)->toArray(false));
        $this->assertEmpty($stream->limit(2, 20)->toArray(false));
    }

    /**
     *
     */
    public function test_matchAll()
    {
        $stream = new FilterStream(new ArrayStream([4, 5, 1, 8, 3]), function ($e) { return $e < 6; });

        $this->assertTrue($stream->matchAll(function ($e) { return $e < 10; }));
        $this->assertFalse($stream->matchAll(function ($e) { return $e % 2 === 0; }));
    }

    /**
     *
     */
    public function test_matchOne()
    {
        $stream = new FilterStream(new ArrayStream([4, 5, 1, 8, 3]), function ($e) { return $e < 6; });

        $this->assertTrue($stream->matchOne(function ($e) { return $e % 2 === 0; }));
        $this->assertFalse($stream->matchOne(function ($e) { return $e > 10; }));
    }
}
