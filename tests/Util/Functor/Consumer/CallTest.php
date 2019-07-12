<?php

namespace Bdf\Collection\Util\Functor\Consumer;

use Bdf\Collection\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * Class CallTest
 */
class CallTest extends TestCase
{
    /**
     *
     */
    public function test_forEach()
    {
        $collection = new ArrayCollection([
            $e1 = $this->createMock(MyCallableInterface::class),
            $e2 = $this->createMock(MyCallableInterface::class),
        ]);

        $e1->expects($this->once())->method('call')->with('foo', 'bar');
        $e2->expects($this->once())->method('call')->with('foo', 'bar');

        $collection->forEach(new Call('call', ['foo', 'bar']));
    }
}

interface MyCallableInterface
{
    public function call(...$parameters);
}
