<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\Accumulators;
use Bdf\Collection\Stream\Collector\Joining;
use Bdf\Collection\Util\Optional;
use PHPUnit\Framework\TestCase;

/**
 * Class EmptyStreamTest
 */
class EmptyStreamTest extends TestCase
{
    /**
     *
     */
    public function test_iterator()
    {
        $this->assertEquals([], iterator_to_array(new EmptyStream()));
    }

    /**
     *
     */
    public function test_transformations()
    {
        $stream = new EmptyStream();

        $this->assertSame($stream, $stream->filter(function () {}));
        $this->assertSame($stream, $stream->map(function () {}));
        $this->assertSame($stream, $stream->distinct());
        $this->assertSame($stream, $stream->sort());
        $this->assertSame($stream, $stream->flatMap(function () {}));
    }

    /**
     *
     */
    public function test_concat_preserve_keys()
    {
        $stream = new ArrayStream([]);

        $this->assertSame($stream, (new EmptyStream())->concat($stream));
    }

    /**
     *
     */
    public function test_concat_no_preserve_keys()
    {
        $stream = new ArrayStream([]);

        $this->assertEquals(new ConcatStream([$stream], false), (new EmptyStream())->concat($stream, false));
    }

    /**
     *
     */
    public function test_toArray()
    {
        $this->assertEquals([], (new EmptyStream())->toArray());
    }

    /**
     *
     */
    public function test_forEach()
    {
        $called = false;

        (new EmptyStream())->forEach(function () use(&$called) {
            $called = true;
        });

        $this->assertFalse($called);
    }

    /**
     *
     */
    public function test_first()
    {
        $this->assertEquals(Optional::empty(), (new EmptyStream())->first());
    }

    /**
     *
     */
    public function test_reduce()
    {
        $this->assertNull((new EmptyStream())->reduce(function () {}));
        $this->assertEquals(0, (new EmptyStream())->reduce(Accumulators::sum()));
        $this->assertEquals(3, (new EmptyStream())->reduce(Accumulators::sum(), 3));
        $this->assertEquals(3, (new EmptyStream())->reduce(function () {}, 3));
    }

    /**
     *
     */
    public function test_collect()
    {
        $this->assertEquals('[]', (new EmptyStream())->collect(new Joining('', '[', ']')));
    }

    /**
     *
     */
    public function test_match()
    {
        $this->assertTrue((new EmptyStream())->matchAll(function () {}));
        $this->assertFalse((new EmptyStream())->matchOne(function () {}));
    }

    /**
     *
     */
    public function test_instance()
    {
        $this->assertInstanceOf(EmptyStream::class, EmptyStream::instance());
        $this->assertSame(EmptyStream::instance(), EmptyStream::instance());
    }
}
