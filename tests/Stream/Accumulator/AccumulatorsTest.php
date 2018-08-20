<?php

namespace Bdf\Collection\Stream\Accumulator;

use Bdf\Collection\Stream\ArrayStream;
use PHPUnit\Framework\TestCase;

/**
 * Class AccumulatorsTest
 */
class AccumulatorsTest extends TestCase
{
    /**
     *
     */
    public function test_instances()
    {
        $this->assertInstanceOf(AccumulatorInterface::class, Accumulators::sum());
        $this->assertInstanceOf(AccumulatorInterface::class, Accumulators::multiply());
        $this->assertInstanceOf(AccumulatorInterface::class, Accumulators::min());
        $this->assertInstanceOf(AccumulatorInterface::class, Accumulators::max());

        $this->assertSame(Accumulators::sum(), Accumulators::sum());
        $this->assertSame(Accumulators::multiply(), Accumulators::multiply());
        $this->assertSame(Accumulators::min(), Accumulators::min());
        $this->assertSame(Accumulators::max(), Accumulators::max());
    }

    /**
     *
     */
    public function test_sum()
    {
        $stream = new ArrayStream([4, 3, 6]);

        $this->assertEquals(13, $stream->reduce(Accumulators::sum()));
    }

    /**
     *
     */
    public function test_multiply()
    {
        $stream = new ArrayStream([4, 3, 6]);

        $this->assertEquals(72, $stream->reduce(Accumulators::multiply()));
    }

    /**
     *
     */
    public function test_min()
    {
        $stream = new ArrayStream([4, 3, 6]);

        $this->assertEquals(3, $stream->reduce(Accumulators::min()));
    }

    /**
     *
     */
    public function test_max()
    {
        $stream = new ArrayStream([4, 3, 6]);

        $this->assertEquals(6, $stream->reduce(Accumulators::max()));
    }
}
