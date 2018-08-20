<?php

namespace Bdf\Collection\Stream;


use Bdf\Collection\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * Class StreamsTest
 */
class StreamsTest extends TestCase
{
    /**
     *
     */
    public function test_wrap()
    {
        $this->assertInstanceOf(EmptyStream::class, Streams::wrap(null));
        $this->assertInstanceOf(EmptyStream::class, Streams::wrap([]));
        $this->assertEquals(new ArrayStream([1, 2, 3]), Streams::wrap([1, 2, 3]));

        $it = new \DirectoryIterator(__DIR__);
        $this->assertEquals(new IteratorStream($it), Streams::wrap($it));

        $collection = new ArrayCollection();
        $this->assertEquals($collection->stream(), Streams::wrap($collection));

        $this->assertEquals(new SingletonStream('hello'), Streams::wrap('hello'));
    }
}
