<?php

namespace Bdf\Collection\Stream\Collector;

use Bdf\Collection\Stream\ArrayStream;
use PHPUnit\Framework\TestCase;

/**
 * Class JoiningTest
 */
class JoiningTest extends TestCase
{
    /**
     *
     */
    public function test_stream()
    {
        $stream = new ArrayStream([1, 2, 3]);

        $this->assertEquals('123', $stream->collect(new Joining()));
        $this->assertEquals('1, 2, 3', $stream->collect(new Joining(', ')));
        $this->assertEquals('[1, 2, 3]', $stream->collect(new Joining(', ', '[', ']')));
    }

    /**
     *
     */
    public function test_finalize_empty()
    {
        $this->assertEquals('', (new Joining())->finalize());
        $this->assertEquals('', (new Joining(', '))->finalize());
        $this->assertEquals('[]', (new Joining(', ', '[', ']'))->finalize());
    }
}
