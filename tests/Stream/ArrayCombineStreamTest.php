<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\Accumulators;
use Bdf\Collection\Stream\Collector\Joining;
use Bdf\Collection\Util\Optional;
use PHPUnit\Framework\TestCase;

/**
 * Class ArrayCombineStreamTest
 */
class ArrayCombineStreamTest extends TestCase
{
    /**
     *
     */
    public function test_invalid_constructor()
    {
        $this->expectException(\InvalidArgumentException::class);

        new ArrayCombineStream([123], []);
    }

    /**
     *
     */
    public function test_iterator_simple_keys()
    {
        $stream = new ArrayCombineStream(['foo', 'bar'], [123, 456]);

        $this->assertEquals([
            'foo' => 123,
            'bar' => 456,
        ], iterator_to_array($stream));
    }

    /**
     *
     */
    public function test_iterator_complex_keys()
    {
        $stream = new ArrayCombineStream(
            [['a' => 'b'], ['b' => 'c']],
            [123, 456]
        );

        $count = 0;

        foreach ($stream as $key => $value) {
            switch ($count++) {
                case 0:
                    $this->assertSame(['a' => 'b'], $key);
                    $this->assertSame(123, $value);
                    break;

                case 1:
                    $this->assertSame(['b' => 'c'], $key);
                    $this->assertSame(456, $value);
                    break;

                default:
                    $this->fail('Invalid key');
            }
        }

        $this->assertSame(2, $count);
    }

    /**
     *
     */
    public function test_toArray()
    {
        $stream = new ArrayCombineStream(['foo', 'bar'], ['a' => 123, 'b' => 456]);

        $this->assertEquals([
            'foo' => 123,
            'bar' => 456,
        ], $stream->toArray());
    }

    /**
     *
     */
    public function test_toArray_complex_keys()
    {
        $stream = new ArrayCombineStream(
            [['a' => 'b'], ['b' => 'c']],
            ['a' => 123, 'b' => 456]
        );

        $this->assertEquals([123, 456], $stream->toArray(false));
    }

    /**
     *
     */
    public function test_map()
    {
        $stream = new ArrayCombineStream(
            [['a' => 'b'], ['b' => 'c']],
            [123, 456]
        );

        $mapStream = $stream->map(function ($e) { return $e * 2; });
        $this->assertInstanceOf(MapStream::class, $mapStream);

        $count = 0;

        foreach ($mapStream as $key => $value) {
            switch ($count++) {
                case 0:
                    $this->assertSame(['a' => 'b'], $key);
                    $this->assertSame(246, $value);
                    break;

                case 1:
                    $this->assertSame(['b' => 'c'], $key);
                    $this->assertSame(912, $value);
                    break;

                default:
                    $this->fail('Invalid key');
            }
        }

        $this->assertSame(2, $count);
    }

    /**
     *
     */
    public function test_mapKey()
    {
        $stream = new ArrayCombineStream(
            ['first_name', 'last_name'],
            ['John', 'Doe']
        );

        $this->assertEquals(['FirstName' => 'John', 'LastName' => 'Doe'], $stream->mapKey(function ($e, $key) {
            return Streams::wrap(explode('_', $key))->map(function ($k) { return ucfirst($k); })->collect(new Joining());
        })->toArray());
    }

    /**
     *
     */
    public function test_filter()
    {
        $stream = new ArrayCombineStream(
            [['a' => 'b'], ['b' => 'c']],
            [123, 456]
        );

        $filter = $stream->filter(function ($e, $k) { return isset($k['b']); });
        $this->assertInstanceOf(FilterStream::class, $filter);

        $count = 0;

        foreach ($filter as $key => $value) {
            switch ($count++) {
                case 0:
                    $this->assertSame(['b' => 'c'], $key);
                    $this->assertSame(456, $value);
                    break;

                default:
                    $this->fail('Invalid key');
            }
        }

        $this->assertSame(1, $count);
    }

    /**
     *
     */
    public function test_distinct()
    {
        $stream = new ArrayCombineStream(
            [['a' => 'b'], ['b' => 'c'], ['c' => 'e']],
            [123, 456, 123]
        );

        $distinct = $stream->distinct();
        $this->assertInstanceOf(DistinctStream::class, $distinct);

        $count = 0;

        foreach ($distinct as $key => $value) {
            switch ($count++) {
                case 0:
                    $this->assertSame(['a' => 'b'], $key);
                    $this->assertSame(123, $value);
                    break;

                case 1:
                    $this->assertSame(['b' => 'c'], $key);
                    $this->assertSame(456, $value);
                    break;

                default:
                    $this->fail('Invalid key');
            }
        }

        $this->assertSame(2, $count);
    }

    /**
     *
     */
    public function test_sort()
    {
        $stream = new ArrayCombineStream(
            [['a'], ['b'], ['c'], ['d']],
            [4, 2, 8, 5]
        );

        $sorted = $stream->sort();

        $this->assertInstanceOf(SortStream::class, $sorted);
        $this->assertEquals([2, 4, 5, 8], $sorted->toArray());
    }

    /**
     *
     */
    public function test_forEach()
    {
        $stream = new ArrayCombineStream(
            [['a' => 'b'], ['b' => 'c']],
            [123, 456]
        );

        $calls = [];

        $stream->forEach(function (...$parameters) use(&$calls) {
            $calls[] = $parameters;
        });

        $this->assertEquals([
            [123, ['a' => 'b']],
            [456, ['b' => 'c']],
        ], $calls);
    }

    /**
     *
     */
    public function test_first()
    {
        $stream = new ArrayCombineStream(
            [['a' => 'b'], ['b' => 'c']],
            [123, 456]
        );

        $this->assertEquals(Optional::of(123), $stream->first());
        $this->assertEquals(Optional::empty(), (new ArrayCombineStream([], []))->first());
    }

    /**
     *
     */
    public function test_reduce()
    {
        $stream = new ArrayCombineStream(
            [['a' => 'b'], ['b' => 'c']],
            [123, 456]
        );

        $this->assertEquals(579, $stream->reduce(Accumulators::sum()));
    }

    /**
     *
     */
    public function test_collect()
    {
        $stream = new ArrayCombineStream(
            [['a' => 'b'], ['b' => 'c']],
            [123, 456]
        );

        $this->assertEquals('123,456', $stream->collect(new Joining(',')));
    }

    /**
     *
     */
    public function test_flatMap()
    {
        $stream = new ArrayCombineStream(
            [1, 2],
            [
                ['value' => [1, 2]],
                ['value' => 3]
            ]
        );

        $this->assertSame([1, 2, 3], $stream->flatMap(function ($e) { return $e['value']; })->toArray());
    }

    /**
     *
     */
    public function test_concat()
    {
        $stream = new ArrayCombineStream(
            ['a', 'b'],
            [123, 456]
        );

        $concat = $stream->concat(new ArrayStream([2, 3]));

        $this->assertInstanceOf(ConcatStream::class, $concat);
        $this->assertSame([
            'a' => 123,
            'b' => 456,
            2, 3
        ], $concat->toArray());
    }

    /**
     *
     */
    public function test_matchAll()
    {
        $stream = new ArrayCombineStream(
            ['a', 'b'],
            [123, 456]
        );

        $this->assertTrue($stream->matchAll(function ($e) { return $e > 100; }));
        $this->assertFalse($stream->matchAll(function ($e) { return $e % 2 === 0; }));
    }

    /**
     *
     */
    public function test_matchOne()
    {
        $stream = new ArrayCombineStream(
            ['a', 'b'],
            [123, 456]
        );

        $this->assertTrue($stream->matchOne(function ($e) { return $e % 2 === 0; }));
        $this->assertFalse($stream->matchAll(function ($e) { return $e < 100; }));
    }

    /**
     *
     */
    public function test_skip()
    {
        $stream = new ArrayCombineStream(
            ['a', 'b', 'c'],
            [123, 456, 789]
        );

        $skip = $stream->skip(2);

        $this->assertInstanceOf(LimitStream::class, $skip);
        $this->assertSame([
            'c' => 789,
        ], $skip->toArray());

        $this->assertEmpty($stream->skip(10)->toArray());
    }

    /**
     *
     */
    public function test_limit()
    {
        $stream = new ArrayCombineStream(
            ['a', 'b', 'c'],
            [123, 456, 789]
        );

        $limit = $stream->limit(2);

        $this->assertInstanceOf(LimitStream::class, $limit);
        $this->assertSame([
            'a' => 123,
            'b' => 456,
        ], $limit->toArray());

        $this->assertSame([
            'b' => 456,
            'c' => 789,
        ], $stream->limit(2, 1)->toArray());
    }
}
