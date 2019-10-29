<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\Accumulators;
use Bdf\Collection\Stream\Collector\Joining;
use Bdf\Collection\Util\Optional;
use PHPUnit\Framework\TestCase;

/**
 * Class MapKeyStreamTest
 */
class MapKeyStreamTest extends TestCase
{
    /**
     *
     */
    public function test_toArray()
    {
        $stream = new MapKeyStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e, $k) { return strtoupper($k); });

        $this->assertSame([
            'FIRSTNAME' => 'John',
            'LASTNAME'  => 'Doe'
        ], $stream->toArray());
    }

    /**
     *
     */
    public function test_forEach()
    {
        $stream = new MapKeyStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e, $k) { return strtoupper($k); });

        $calls = [];

        $stream->forEach(function ($e, $k) use(&$calls) {
            $calls[] = [$e, $k];
        });

        $this->assertEquals([
            ['John', 'FIRSTNAME'],
            ['Doe', 'LASTNAME'],
        ], $calls);
    }

    /**
     *
     */
    public function test_filter()
    {
        $stream = new MapKeyStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e, $k) { return strtoupper($k); });

        $filterStream = $stream->filter(function ($e) { return strpos($e, 'J') !== false; });

        $this->assertInstanceOf(FilterStream::class, $filterStream);
        $this->assertEquals(['FIRSTNAME' => 'John'], $filterStream->toArray());
    }

    /**
     *
     */
    public function test_map()
    {
        $stream = new MapKeyStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e, $k) { return strtoupper($k); });

        $mapStream = $stream->map(function ($e) { return $e[0]; });

        $this->assertInstanceOf(MapStream::class, $mapStream);
        $this->assertEquals([
            'FIRSTNAME' => 'J',
            'LASTNAME'  => 'D'
        ], $mapStream->toArray());
    }

    /**
     *
     */
    public function test_mapKey()
    {
        $stream = new MapKeyStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e, $k) { return strtoupper($k); });

        $mapStream = $stream->mapKey(function ($e, $k) { return $k[0]; });

        $this->assertInstanceOf(MapKeyStream::class, $mapStream);
        $this->assertEquals([
            'F' => 'John',
            'L'  => 'Doe'
        ], $mapStream->toArray());
    }

    /**
     *
     */
    public function test_iterator()
    {
        $stream = new MapKeyStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e, $k) { return strtoupper($k); });

        $this->assertSame([
            'FIRSTNAME' => 'John',
            'LASTNAME'  => 'Doe'
        ], iterator_to_array($stream));
    }

    /**
     *
     */
    public function test_first()
    {
        $stream = new MapKeyStream(new ArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]), function ($e, $k) { return strtoupper($k); });

        $this->assertEquals(Optional::of('John'), $stream->first());
        $this->assertEquals(Optional::empty(), (new MapKeyStream(new EmptyStream(), function () {}))->first());
    }

    /**
     *
     */
    public function test_distinct()
    {
        $stream = new MapKeyStream(new ArrayStream([4, 8, 4, 5, 1, 7, 1]), function ($e) { return $e * 2; });

        $distinctStream = $stream->distinct();

        $this->assertInstanceOf(DistinctStream::class, $distinctStream);
        $this->assertEquals([8 => 4, 16 => 8, 10 => 5, 2 => 1, 14 => 7], $distinctStream->toArray());
    }

    /**
     *
     */
    public function test_sort()
    {
        $stream = new MapKeyStream(new ArrayStream([4, 5, 1, 8, 3]), function ($e) { return $e * 2; });

        $sortStream = $stream->sort(null, true);

        $this->assertInstanceOf(SortStream::class, $sortStream);
        $this->assertSame([2 => 1, 6 => 3, 8 => 4, 10 => 5, 16 => 8], $sortStream->toArray());
    }

    /**
     *
     */
    public function test_reduce()
    {
        $stream = new MapKeyStream(new ArrayStream([4, 5, 1]), function ($e) { return $e * 2; });

        $this->assertEquals(10, $stream->reduce(Accumulators::sum()));
    }

    /**
     *
     */
    public function test_collect()
    {
        $stream = new MapKeyStream(new ArrayStream([4, 5, 1]), function ($e) { return $e * 2; });

        $this->assertEquals('4:5:1', $stream->collect(new Joining(':')));
    }

    /**
     *
     */
    public function test_concat()
    {
        $stream = new MapKeyStream(new ArrayStream([4, 5, 1]), function ($e) { return $e * 2; });

        $this->assertEquals([8 => 4, 10 => 5, 2 => 1, 0 => 5, 1 => 3], $stream->concat(new ArrayStream([5, 3]), true)->toArray());
    }

    /**
     *
     */
    public function test_flatMap()
    {
        $stream = new MapKeyStream(new ArrayStream([[1, 2], [3, 4]]), function ($e) { return array_sum($e); });

        $this->assertEquals(['3_0' => 1, '3_1' => 2, '7_0' => 3, '7_1' => 4], $stream->flatMap(function ($e, $key) {
            $mapped = [];

            foreach ($e as $k => $v) {
                $mapped[$key.'_'.$k] = $v;
            }

            return $mapped;
        }, true)->toArray());
    }

    /**
     *
     */
    public function test_skip()
    {
        $stream = new MapKeyStream(new ArrayStream([4, 5, 1]), function ($e) { return $e * 2; });

        $this->assertInstanceOf(LimitStream::class, $stream->skip(2));
        $this->assertEquals([2 => 1], $stream->skip(2)->toArray());
        $this->assertEmpty($stream->skip(100)->toArray());
    }

    /**
     *
     */
    public function test_limit()
    {
        $stream = new MapKeyStream(new ArrayStream([4, 5, 1]), function ($e) { return $e * 2; });

        $this->assertInstanceOf(LimitStream::class, $stream->limit(2));
        $this->assertEquals([8 => 4, 10 => 5], $stream->limit(2)->toArray());
        $this->assertEquals([10 => 5, 2 => 1], $stream->limit(2, 1)->toArray());
        $this->assertEmpty($stream->limit(1, 100)->toArray());
    }

    /**
     *
     */
    public function test_matchAll()
    {
        $stream = new MapKeyStream(new ArrayStream([4, 5, 1]), function ($e) { return $e * 2; });

        $this->assertTrue($stream->matchAll(function ($e) { return $e < 20; }));
        $this->assertFalse($stream->matchAll(function ($e) { return $e % 4 === 0; }));
    }

    /**
     *
     */
    public function test_matchOne()
    {
        $stream = new MapKeyStream(new ArrayStream([4, 5, 1]), function ($e) { return $e * 2; });

        $this->assertTrue($stream->matchOne(function ($e) { return $e % 4 === 0; }));
        $this->assertFalse($stream->matchOne(function ($e) { return $e > 20; }));
    }
}
