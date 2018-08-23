<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\Accumulators;
use Bdf\Collection\Stream\Collector\Joining;
use Bdf\Collection\Util\Optional;
use PHPUnit\Framework\TestCase;

/**
 * Class MapStreamTest
 */
class MapStreamTest extends TestCase
{
    /**
     *
     */
    public function test_toArray()
    {
        $stream = new MapStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e) { return strtoupper($e); });

        $this->assertSame([
            'firstName' => 'JOHN',
            'lastName'  => 'DOE'
        ], $stream->toArray());
        $this->assertSame(['JOHN', 'DOE'], $stream->toArray(false));
    }

    /**
     *
     */
    public function test_forEach()
    {
        $stream = new MapStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e) { return strtoupper($e); });

        $calls = [];

        $stream->forEach(function ($e, $k) use(&$calls) {
            $calls[] = [$e, $k];
        });

        $this->assertEquals([
            ['JOHN', 'firstName'],
            ['DOE', 'lastName'],
        ], $calls);
    }

    /**
     *
     */
    public function test_filter()
    {
        $stream = new MapStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e) { return strtoupper($e); });

        $filterStream = $stream->filter(function ($e) { return strpos($e, 'J') !== false; });

        $this->assertInstanceOf(FilterStream::class, $filterStream);
        $this->assertEquals(['firstName' => 'JOHN'], $filterStream->toArray());
    }

    /**
     *
     */
    public function test_map()
    {
        $stream = new MapStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e) { return strtoupper($e); });

        $mapStream = $stream->map(function ($e) { return $e[0]; });

        $this->assertInstanceOf(MapStream::class, $mapStream);
        $this->assertEquals([
            'firstName' => 'J',
            'lastName'  => 'D'
        ], $mapStream->toArray());
    }

    /**
     *
     */
    public function test_iterator()
    {
        $stream = new MapStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e) { return strtoupper($e); });

        $this->assertSame([
            'firstName' => 'JOHN',
            'lastName'  => 'DOE'
        ], iterator_to_array($stream));
    }

    /**
     *
     */
    public function test_first()
    {
        $stream = new MapStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e) { return strtoupper($e); });

        $this->assertEquals(Optional::of('JOHN'), $stream->first());
        $this->assertEquals(Optional::empty(), (new MapStream(new EmptyStream(), function () {}))->first());
    }

    /**
     *
     */
    public function test_distinct()
    {
        $stream = new MapStream(new ArrayStream([4, 8, 4, 5, 1, 7, 1]), function ($e) { return $e * 2; });

        $distinctStream = $stream->distinct();

        $this->assertInstanceOf(DistinctStream::class, $distinctStream);
        $this->assertEquals([8, 16, 10, 2, 14], array_values($distinctStream->toArray()));
    }

    /**
     *
     */
    public function test_sort()
    {
        $stream = new MapStream(new ArrayStream([4, 5, 1, 8, 3]), function ($e) { return $e * 2; });

        $sortStream = $stream->sort();

        $this->assertInstanceOf(SortStream::class, $sortStream);
        $this->assertEquals([2, 6, 8, 10, 16], $sortStream->toArray());
    }

    /**
     *
     */
    public function test_reduce()
    {
        $stream = new MapStream(new ArrayStream([4, 5, 1]), function ($e) { return $e * 2; });

        $this->assertEquals(20, $stream->reduce(Accumulators::sum()));
    }

    /**
     *
     */
    public function test_collect()
    {
        $stream = new MapStream(new ArrayStream([4, 5, 1]), function ($e) { return $e * 2; });

        $this->assertEquals('8:10:2', $stream->collect(new Joining(':')));
    }

    /**
     *
     */
    public function test_concat()
    {
        $stream = new MapStream(new ArrayStream([4, 5, 1]), function ($e) { return $e * 2; });

        $this->assertEquals([8, 10, 2, 5, 3], $stream->concat(new ArrayStream([5, 3]), false)->toArray());
    }

    /**
     *
     */
    public function test_flatMap()
    {
        $stream = new MapStream(new ArrayStream([[1, 2], [3, 4]]), function ($e) { $e[] = 0; return $e; });

        $this->assertEquals([1, 2, 0, 3, 4, 0], $stream->flatMap(function ($e) { return $e; })->toArray());
    }

    /**
     *
     */
    public function test_matchAll()
    {
        $stream = new MapStream(new ArrayStream([4, 5, 1]), function ($e) { return $e * 2; });

        $this->assertTrue($stream->matchAll(function ($e) { return $e < 20; }));
        $this->assertFalse($stream->matchAll(function ($e) { return $e % 4 === 0; }));
    }

    /**
     *
     */
    public function test_matchOne()
    {
        $stream = new MapStream(new ArrayStream([4, 5, 1]), function ($e) { return $e * 2; });

        $this->assertTrue($stream->matchOne(function ($e) { return $e % 4 === 0; }));
        $this->assertFalse($stream->matchOne(function ($e) { return $e > 20; }));
    }
}
