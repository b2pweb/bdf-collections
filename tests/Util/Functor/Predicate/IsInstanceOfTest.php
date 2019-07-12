<?php

namespace Bdf\Collection\Util\Functor\Predicate;

use Bdf\Collection\Stream\Streams;
use PHPUnit\Framework\TestCase;

/**
 * Class IsInstanceOfTest
 */
class IsInstanceOfTest extends TestCase
{
    /**
     *
     */
    public function test_filter()
    {
        $result = Streams::wrap([5, $expected = new \stdClass(), new \ArrayObject()])
            ->filter(new IsInstanceOf(\stdClass::class))
            ->toArray(false)
        ;

        $this->assertSame([$expected], $result);
    }
}
