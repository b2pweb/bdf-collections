<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\Accumulators;
use Bdf\Collection\Stream\Collector\Joining;
use Bdf\Collection\Util\Optional;
use PHPUnit\Framework\TestCase;

/**
 * Class FlatMapStreamTest
 */
class FlatMapStreamTest extends TestCase
{
    /**
     *
     */
    public function test_toArray()
    {
        $stream = new ArrayStream([
            ['values' => [4, 7, 8]],
            ['values' => [1, 5, 2]],
            ['values' => [4, 2, 9]],
        ]);

        $flat = new FlatMapStream($stream, function ($e) { return $e['values']; });

        $this->assertEquals([4, 7, 8, 1, 5, 2, 4, 2, 9], iterator_to_array($flat));
        $this->assertEquals([4, 7, 8, 1, 5, 2, 4, 2, 9], $flat->toArray());
    }

    /**
     *
     */
    public function test_with_preserve_keys()
    {
        $stream = new ArrayStream([
            ['values' => ['foo' => 'bar']],
            ['values' => ['bar' => 'baz']],
        ]);

        $flat = new FlatMapStream($stream, function ($e) { return $e['values']; }, true);

        $this->assertSame([
            'foo' => 'bar',
            'bar' => 'baz',
        ], iterator_to_array($flat));
    }

    /**
     *
     */
    public function test_with_empty_values()
    {
        $stream = new ArrayStream([
            ['values' => []],
            ['values' => [4, 7, 8]],
            ['values' => []],
            ['values' => 12],
            ['values' => null]
        ]);

        $flat = new FlatMapStream($stream, function ($e) { return $e['values']; });

        $this->assertEquals([4, 7, 8, 12], iterator_to_array($flat));
    }

    /**
     *
     */
    public function test_with_empty_stream()
    {
        $flat = new FlatMapStream(new EmptyStream(), function ($e) { return $e['values']; });

        $this->assertEquals([], iterator_to_array($flat));
    }

    /**
     *
     */
    public function test_distinct()
    {
        $stream = new ArrayStream([
            ['values' => [4, 7, 8]],
            ['values' => [1, 5, 2]],
            ['values' => [4, 2, 9]],
        ]);

        $flat = new FlatMapStream($stream, function ($e) { return $e['values']; });

        $this->assertEquals([4, 7, 8, 1, 5, 2, 9], $flat->distinct()->toArray(false));
    }

    /**
     *
     */
    public function test_map()
    {
        $stream = new ArrayStream([
            ['values' => [4, 7, 8]],
            ['values' => [1, 5, 2]],
        ]);

        $flat = new FlatMapStream($stream, function ($e) { return $e['values']; });

        $this->assertEquals([6, 9, 10, 3, 7, 4], $flat->map(function ($e) { return $e + 2; })->toArray(false));
    }

    /**
     *
     */
    public function test_filter()
    {
        $stream = new ArrayStream([
            ['values' => [4, 7, 8]],
            ['values' => [1, 5, 2]],
        ]);

        $flat = new FlatMapStream($stream, function ($e) { return $e['values']; });

        $this->assertEquals([4, 8, 2], $flat->filter(function ($e) { return $e % 2 === 0; })->toArray(false));
    }

    /**
     *
     */
    public function test_sort()
    {
        $stream = new ArrayStream([
            ['values' => [4, 7, 8]],
            ['values' => [1, 5, 2]],
        ]);

        $flat = new FlatMapStream($stream, function ($e) { return $e['values']; });

        $this->assertEquals([1, 2, 4, 5, 7, 8], $flat->sort()->toArray(false));
    }

    /**
     *
     */
    public function test_concat()
    {
        $stream = new ArrayStream([
            ['values' => [4, 7, 8]],
            ['values' => [1, 5, 2]],
        ]);

        $flat = new FlatMapStream($stream, function ($e) { return $e['values']; });

        $this->assertEquals([4, 7, 8, 1, 5, 2, 3], $flat->concat(new SingletonStream(3))->toArray(false));
    }

    /**
     *
     */
    public function test_forEach()
    {
        $stream = new ArrayStream([
            ['values' => [4, 7]],
            ['values' => [1, 5]],
        ]);

        $flat = new FlatMapStream($stream, function ($e) { return $e['values']; });

        $calls = [];
        $flat->forEach(function (...$p) use(&$calls) { $calls[] = $p; });

        $this->assertEquals([
            [4, 0],
            [7, 1],
            [1, 2],
            [5, 3],
        ], $calls);
    }

    /**
     *
     */
    public function test_first()
    {
        $stream = new ArrayStream([
            ['values' => [4, 7]],
            ['values' => [1, 5]],
        ]);

        $flat = new FlatMapStream($stream, function ($e) { return $e['values']; });

        $this->assertEquals(Optional::of(4), $flat->first());
        $this->assertEquals(Optional::empty(), (new FlatMapStream(new EmptyStream(), function () {}))->first());
    }

    /**
     *
     */
    public function test_reduce()
    {
        $stream = new ArrayStream([
            ['values' => [4, 7]],
            ['values' => [1, 5]],
        ]);

        $flat = new FlatMapStream($stream, function ($e) { return $e['values']; });

        $this->assertEquals(17, $flat->reduce(Accumulators::sum()));
    }

    /**
     *
     */
    public function test_collect()
    {
        $stream = new ArrayStream([
            ['values' => [4, 7]],
            ['values' => [1, 5]],
        ]);

        $flat = new FlatMapStream($stream, function ($e) { return $e['values']; });

        $this->assertEquals('4,7,1,5', $flat->collect(new Joining(',')));
    }

    /**
     *
     */
    public function test_flatMap()
    {
        $stream = new ArrayStream([
            [['values' => [4, 7]], ['values' => [3, 4]]],
            [['values' => [1, 5]]],
        ]);

        $flat = new FlatMapStream($stream, function ($e) { return $e; });

        $this->assertEquals([4, 7, 3, 4, 1, 5], $flat->flatMap(function ($e) { return $e['values']; })->toArray());
    }

    /**
     *
     */
    public function test_matchAll()
    {
        $stream = new ArrayStream([
            ['values' => [4, 7]],
            ['values' => [1, 5]],
        ]);

        $flat = new FlatMapStream($stream, function ($e) { return $e['values']; });

        $this->assertTrue($flat->matchAll(function ($e) { return $e < 10; }));
        $this->assertFalse($flat->matchAll(function ($e) { return $e % 2 === 0; }));
    }

    /**
     *
     */
    public function test_matchOne()
    {
        $stream = new ArrayStream([
            ['values' => [4, 7]],
            ['values' => [1, 5]],
        ]);

        $flat = new FlatMapStream($stream, function ($e) { return $e['values']; });

        $this->assertTrue($flat->matchOne(function ($e) { return $e % 2 === 0; }));
        $this->assertFalse($flat->matchOne(function ($e) { return $e > 10; }));
    }
}
