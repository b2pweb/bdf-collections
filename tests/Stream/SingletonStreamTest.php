<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\AccumulatorInterface;
use Bdf\Collection\Stream\Accumulator\Accumulators;
use Bdf\Collection\Stream\Collector\Joining;
use Bdf\Collection\Util\Optional;
use PHPUnit\Framework\TestCase;

/**
 * Class SingletonStreamTest
 */
class SingletonStreamTest extends TestCase
{
    /**
     *
     */
    public function test_toArray()
    {
        $stream = new SingletonStream('bar', 'foo');

        $this->assertEquals(['foo' => 'bar'], $stream->toArray());
        $this->assertEquals(['bar'], $stream->toArray(false));
    }

    /**
     *
     */
    public function test_iterator()
    {
        $stream = new SingletonStream('bar', 'foo');

        $this->assertEquals(['foo' => 'bar'], iterator_to_array($stream));
        $this->assertFalse($stream->valid());
    }

    /**
     *
     */
    public function test_map()
    {
        $stream = new SingletonStream('bar', 'foo');

        $mappedStream = $stream->map(function ($e) { return strtoupper($e); });

        $this->assertInstanceOf(SingletonStream::class, $mappedStream);
        $this->assertEquals(['foo' => 'BAR'], $mappedStream->toArray());
    }

    /**
     *
     */
    public function test_filter()
    {
        $stream = new SingletonStream('bar', 'foo');

        $this->assertSame($stream, $stream->filter(function ($e) { return $e[0] === 'b'; }));
        $this->assertInstanceOf(EmptyStream::class, $stream->filter(function ($e) { return $e[0] === 'e'; }));
    }

    /**
     *
     */
    public function test_distinct()
    {
        $stream = new SingletonStream('bar', 'foo');

        $this->assertSame($stream, $stream->distinct());
    }

    /**
     *
     */
    public function test_sort()
    {
        $stream = new SingletonStream('bar', 'foo');

        $this->assertSame($stream, $stream->sort(null, true));
        $this->assertEquals(new SingletonStream('bar'), $stream->sort());
    }

    /**
     *
     */
    public function test_sort_no_key()
    {
        $stream = new SingletonStream('bar');

        $this->assertSame($stream, $stream->sort(null, true));
        $this->assertSame($stream, $stream->sort());
    }

    /**
     *
     */
    public function test_forEach()
    {
        $stream = new SingletonStream('bar', 'foo');


        $calls = [];

        $stream->forEach(function ($e, $k) use(&$calls) {
            $calls[] = [$e, $k];
        });

        $this->assertEquals([
            ['bar', 'foo'],
        ], $calls);

        $this->assertFalse($stream->valid());
    }

    /**
     *
     */
    public function test_first()
    {
        $stream = new SingletonStream('bar', 'foo');

        $this->assertEquals(Optional::of('bar'), $stream->first());
    }

    /**
     *
     */
    public function test_reduce()
    {
        $stream = new SingletonStream(15);

        $this->assertEquals(15, $stream->reduce(Accumulators::sum()));
        $this->assertEquals(18, $stream->reduce(Accumulators::sum(), 3));

        $accumulator = $this->createMock(AccumulatorInterface::class);
        $accumulator->expects($this->once())
            ->method('__invoke')
            ->with(null, 15)
            ->willReturn(5)
        ;

        $this->assertEquals(5, $stream->reduce($accumulator));
    }

    /**
     *
     */
    public function test_collect()
    {
        $stream = new SingletonStream(15);

        $this->assertEquals('[15]', $stream->collect(new Joining(':', '[', ']')));
    }

    /**
     *
     */
    public function test_concat()
    {
        $stream = new SingletonStream(15);

        $this->assertEquals([15, 2, 1], $stream->concat(new ArrayStream([2, 1]), false)->toArray());
    }

    /**
     *
     */
    public function test_match()
    {
        $stream = new SingletonStream(15);

        $this->assertTrue($stream->matchOne(function ($e) { return $e % 5 === 0; }));
        $this->assertFalse($stream->matchOne(function ($e) { return $e % 2 === 0; }));
        $this->assertTrue($stream->matchAll(function ($e) { return $e % 5 === 0; }));
        $this->assertFalse($stream->matchAll(function ($e) { return $e % 2 === 0; }));
    }
}
