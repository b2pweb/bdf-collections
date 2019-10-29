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
    public function test_mapKey()
    {
        $stream = new MutableArrayStream([
            'firstName' => 'John',
            'lastName'  => 'Doe'
        ]);

        $this->assertSame($stream, $stream->mapKey(function ($e, $k) { return strtoupper($e[0].$k[0]); }));
        $this->assertEquals([
            'JF' => 'John',
            'DL'  => 'Doe'
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

    /**
     *
     */
    public function test_concat_preserve_keys_with_mutableStream()
    {
        $stream = new MutableArrayStream([
            'John'   => 'Doe',
            'Mickey' => 'Mouse',
        ]);

        $this->assertSame($stream, $stream->concat(new MutableArrayStream(['Donald' => 'Duck'])));

        $this->assertSame([
            'John'   => 'Doe',
            'Mickey' => 'Mouse',
            'Donald' => 'Duck'
        ], $stream->toArray());
    }

    /**
     *
     */
    public function test_concat_no_preserve_keys_with_mutableStream()
    {
        $stream = new MutableArrayStream([7, 4, 2]);

        $this->assertSame($stream, $stream->concat(new MutableArrayStream([3, 8, 1]), false));

        $this->assertSame([7, 4, 2, 3, 8, 1], $stream->toArray());
    }

    /**
     *
     */
    public function test_concat()
    {
        $stream = new MutableArrayStream([7, 4, 2]);

        $concat = $stream->concat(new ArrayStream([3, 8, 1]), false);

        $this->assertInstanceOf(ConcatStream::class, $concat);
        $this->assertSame([7, 4, 2, 3, 8, 1], $concat->toArray());
    }

    /**
     *
     */
    public function test_flatMap_preserve_keys()
    {
        $stream = new MutableArrayStream([
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
        $stream = new MutableArrayStream([
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
    public function test_skip()
    {
        $stream = new MutableArrayStream([7, 4, 2]);

        $this->assertSame($stream, $stream->skip(2));
        $this->assertSame([2], $stream->toArray(false));
        $this->assertSame([2 => 2], $stream->toArray());
        $this->assertEmpty($stream->skip(100)->toArray(false));
    }

    /**
     *
     */
    public function test_limit()
    {
        $stream = new MutableArrayStream([7, 4, 2]);

        $this->assertSame($stream, $stream->limit(2));
        $this->assertSame([7, 4], $stream->toArray(false));
        $this->assertSame([0 => 7, 1 => 4], $stream->toArray());
        $this->assertSame([4], $stream->limit(5, 1)->toArray(false));
        $this->assertEmpty($stream->limit(1, 100)->toArray(false));
    }

    /**
     *
     */
    public function test_matchAll()
    {
        $stream = new MutableArrayStream([4, 8, 2]);

        $this->assertTrue($stream->matchAll(function ($e) { return $e % 2 === 0; }));
        $this->assertFalse($stream->matchAll(function ($e) { return $e % 4 === 0; }));
    }

    /**
     *
     */
    public function test_matchOne()
    {
        $stream = new MutableArrayStream([4, 8, 2]);

        $this->assertTrue($stream->matchOne(function ($e) { return $e % 4 === 0; }));
        $this->assertFalse($stream->matchOne(function ($e) { return $e % 2 === 1; }));
    }
}
