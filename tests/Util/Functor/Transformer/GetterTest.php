<?php

namespace Bdf\Collection\Util\Functor\Transformer;

use Bdf\Collection\Stream\ArrayStream;
use PHPUnit\Framework\TestCase;

/**
 * Class GetterTest
 */
class GetterTest extends TestCase
{
    /**
     *
     */
    public function test_stream()
    {
        $stream = new ArrayStream([
            new \ReflectionClass($this),
            new \ReflectionClass(\Exception::class),
        ]);

        $this->assertEquals([self::class, \Exception::class], $stream->map(new Getter('getName'))->toArray());
    }
}
