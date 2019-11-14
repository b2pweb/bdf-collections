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
    public function test_mapKey()
    {
        $stream = new SortStream(new ArrayStream([4, 7, 5, 2, 3]));

        $mapStream = $stream->mapKey(function ($e) { return $e * 2; });

        $this->assertInstanceOf(MapKeyStream::class, $mapStream);
        $this->assertSame([4 => 2, 6 => 3, 8 => 4, 10 => 5, 14 => 7], $mapStream->toArray());
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
    public function test_iterator_manual()
    {
        $stream = new SortStream(new ArrayStream([4, 7, 5, 2, 3]));

        $this->assertTrue($stream->valid());
        $this->assertEquals(2, $stream->current());
        $this->assertEquals(3, $stream->key());
        $stream->next();

        $this->assertTrue($stream->valid());
        $this->assertEquals(3, $stream->current());
        $this->assertEquals(4, $stream->key());

        $stream->next();
        $stream->next();
        $stream->next();

        $this->assertTrue($stream->valid());
        $this->assertEquals(7, $stream->current());
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

    /**
     *
     */
    public function test_concat()
    {
        $stream = new SortStream(new ArrayStream([4, 5, 1]), null, false);

        $this->assertSame([1, 4, 5, 3, 2], $stream->concat(new ArrayStream([3, 2]), false)->toArray());
    }

    /**
     *
     */
    public function test_flatMap()
    {
        $stream = new SortStream(new ArrayStream([[2, 3], [2, 5], [1, 4]]), null, false);

        $this->assertSame([1, 4, 2, 3, 2, 5], $stream->flatMap(function ($e) { return $e; })->toArray());
    }

    /**
     *
     */
    public function test_skip()
    {
        $stream = new SortStream(new ArrayStream([4, 5, 1]), null, false);

        $this->assertInstanceOf(LimitStream::class, $stream->skip(2));
        $this->assertSame([5], $stream->skip(2)->toArray(false));
        $this->assertSame([], $stream->skip(10)->toArray(false));
    }

    /**
     *
     */
    public function test_limit()
    {
        $stream = new SortStream(new ArrayStream([4, 5, 1]), null, false);

        $this->assertInstanceOf(LimitStream::class, $stream->limit(2));
        $this->assertSame([1, 4], $stream->limit(2)->toArray(false));
        $this->assertSame([4, 5], $stream->limit(2, 1)->toArray(false));
        $this->assertSame([], $stream->limit(2, 10)->toArray(false));
    }

    /**
     *
     */
    public function test_matchAll()
    {
        $stream = new SortStream(new ArrayStream([4, 5, 1]));

        $this->assertTrue($stream->matchAll(function ($e) { return $e < 10; }));
        $this->assertFalse($stream->matchAll(function ($e) { return $e % 2 === 0; }));
    }

    /**
     *
     */
    public function test_matchOne()
    {
        $stream = new SortStream(new ArrayStream([4, 5, 1]));

        $this->assertTrue($stream->matchOne(function ($e) { return $e % 2 === 0; }));
        $this->assertFalse($stream->matchOne(function ($e) { return $e > 10; }));
    }
}
