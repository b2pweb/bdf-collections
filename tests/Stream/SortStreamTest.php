<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\Accumulators;
use Bdf\Collection\Stream\Collector\Joining;
use Bdf\Collection\Util\Optional;
use PHPUnit\Framework\TestCase;

/**
 * Class SortStreamTest
 */
class SortStreamTest extends TestCase
{
    /**
     *
     */
    public function test_with_assoc()
    {
        $stream = new SortStream(new ArrayStream([
            'a' => 12,
            'b' => 3,
            'c' => 25,
        ]));

        $this->assertSame([
            'b' => 3,
            'a' => 12,
            'c' => 25
        ], $stream->toArray());
    }

    /**
     *
     */
    public function test_with_assoc_custom_comparator_not_preserve_key()
    {
        $stream = new SortStream(
            new ArrayStream([
                'a' => 12,
                'c' => 25,
                'b' => 3,
            ]),
            function ($a, $b) { return [$a % 2, $a] <=> [$b % 2, $b]; },
            false
        );

        $this->assertSame([12, 3, 25], $stream->toArray());
    }

    /**
     *
     */
    public function test_with_assoc_custom_comparator()
    {
        $stream = new SortStream(
            new ArrayStream([
                'a' => 12,
                'b' => 25,
                'c' => 3,
            ]),
            function ($a, $b) { return [$a % 2, $a] <=> [$b % 2, $b]; }
        );

        $this->assertSame([
            'a' => 12,
            'c' => 3,
            'b' => 25
        ], $stream->toArray());
    }

    /**
     *
     */
    public function test_toArray()
    {
        $stream = new SortStream(new ArrayStream([4, 7, 5, 2, 3]));

        $this->assertSame([3 => 2, 4 => 3, 0 => 4, 2 => 5, 1 => 7], $stream->toArray());
    }

    /**
     *
     */
    public function test_toArray_not_preserve_keys()
    {
        $stream = new SortStream(new ArrayStream([4, 7, 5, 2, 3]));

        $this->assertSame([2, 3, 4, 5, 7], $stream->toArray(false));
    }

    /**
     *
     */
    public function test_toArray_with_custom_comparator_not_preserve_keys()
    {
        $stream = new SortStream(
            new ArrayStream([4, 7, 5, 2, 3]),
            function ($a, $b) { return [$b % 2, $a] <=> [$a % 2, $b]; },
            false
        );

        $this->assertEquals([3, 5, 7, 2, 4], $stream->toArray());
    }

    /**
     *
     */
    public function test_iterator_with_custom_comparator()
    {
        $stream = new SortStream(
            new ArrayStream([4, 7, 5, 2, 3]),
            function ($a, $b) { return [$b % 2, $a] <=> [$a % 2, $b]; }
        );

        $this->assertEquals([3, 5, 7, 2, 4], iterator_to_array($stream, false));
    }

    /**
     *
     */
    public function test_forEach()
    {
        $stream = new SortStream(new ArrayStream([4, 7, 5, 2, 3]));

        $calls = [];

        $stream->forEach(function ($e, $k) use(&$calls) {
            $calls[] = [$e, $k];
        });

        $this->assertEquals([
            [2, 3],
            [3, 4],
            [4, 0],
            [5, 2],
            [7, 1],
        ], $calls);
    }

    /**
     *
     */
    public function test_filter()
    {
        $stream = new SortStream(new ArrayStream([4, 7, 5, 2, 3]));

        $filterStream = $stream->filter(function ($e) { return $e < 5; });

        $this->assertInstanceOf(FilterStream::class, $filterStream);
        $this->assertEquals([2, 3, 4], array_values($filterStream->toArray()));
    }

    /**
     *
     */
    public function test_map()
    {
        $stream = new SortStream(new ArrayStream([4, 7, 5, 2, 3]));

        $mapStream = $stream->map(function ($e) { return $e * 2; });

        $this->assertInstanceOf(MapStream::class, $mapStream);
        $this->assertEquals([4, 6, 8, 10, 14], array_values($mapStream->toArray()));
    }

    /**
     *
     */
    public function test_iterator()
    {
        $stream = new SortStream(new ArrayStream([4, 7, 5, 2, 3]));

        $this->assertSame([
            3 => 2,
            4 => 3,
            0 => 4,
            2 => 5,
            1 => 7,
        ], iterator_to_array($stream));
    }

    /**
     *
     */
    public function test_first()
    {
        $stream = new SortStream(new ArrayStream([4, 7, 5, 2, 3]));

        $this->assertEquals(Optional::of(2), $stream->first());

        $stream = new SortStream(new ArrayStream([]));
        $this->assertEquals(Optional::empty(), $stream->first());
    }

    /**
     *
     */
    public function test_first_with_custom_comparator()
    {
        $stream = new SortStream(
            new ArrayStream([4, 7, 5, 2, 3]),
            function ($a, $b) { return [$b % 2, $a] <=> [$a % 2, $b]; }
        );

        $this->assertEquals(Optional::of(3), $stream->first());
    }

    /**
     *
     */
    public function test_distinct()
    {
        $stream = new SortStream(new ArrayStream([4, 7, 5, 2, 3]));

        $distinctStream = $stream->distinct(function ($e) { return $e % 2; });

        $this->assertInstanceOf(DistinctStream::class, $distinctStream);
        $this->assertEquals([2, 3], array_values($distinctStream->toArray()));
    }

    /**
     *
     */
    public function test_sort()
    {
        $stream = new SortStream(new ArrayStream([4, 5, 1, 8, 3]));

        $sortStream = $stream->sort(function ($a, $b) { return $a % 2 <=> $b % 2; });

        $this->assertInstanceOf(SortStream::class, $sortStream);
        $this->assertEquals([4, 8, 1, 3, 5], array_values($sortStream->toArray()));
    }

    /**
     *
     */
    public function test_reduce()
    {
        $stream = new SortStream(new ArrayStream([4, 5, 1]));

        $this->assertEquals(10, $stream->reduce(Accumulators::sum()));
    }

    /**
     *
     */
    public function test_collect()
    {
        $stream = new SortStream(new ArrayStream([4, 5, 1]));

        $this->assertEquals('1:4:5', $stream->collect(new Joining(':')));
    }
}
