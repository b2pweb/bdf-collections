<?php

namespace Bdf\Collection\Util;

use Bdf\Collection\Stream\EmptyStream;
use PHPUnit\Framework\TestCase;

/**
 * Class EmptyOptionalTest
 */
class EmptyOptionalTest extends TestCase
{
    /**
     *
     */
    public function test_filter()
    {
        $this->assertSame(EmptyOptional::instance(), EmptyOptional::instance()->filter(function () {}));
        $this->assertSame(EmptyOptional::instance(), EmptyOptional::instance()->map(function () {}));
    }

    /**
     *
     */
    public function test_apply()
    {
        $called = false;

        EmptyOptional::instance()->apply(function () use(&$called) { $called = true; });

        $this->assertFalse($called);
    }

    /**
     *
     */
    public function test_or()
    {
        $this->assertEquals(123, EmptyOptional::instance()->or(123));
    }

    /**
     *
     */
    public function test_orThrows_with_exception_class()
    {
        $this->expectException(\DomainException::class);

        EmptyOptional::instance()->orThrows(\DomainException::class);
    }

    /**
     *
     */
    public function test_orThrows_with_custom_exception()
    {
        try {
            EmptyOptional::instance()->orThrows($myException = new \Exception());

            $this->fail('Exception exception');
        } catch (\Exception $e) {
            $this->assertSame($myException, $e);
        }
    }

    /**
     *
     */
    public function test_orSupply()
    {
        $this->assertEquals(123, EmptyOptional::instance()->orSupply(function () { return 123; }));
    }

    /**
     *
     */
    public function test_present()
    {
        $this->assertFalse(EmptyOptional::instance()->present());
    }

    /**
     *
     */
    public function test_magic_methods()
    {
        $this->assertSame(EmptyOptional::instance(), EmptyOptional::instance()->myMethod());
        $this->assertSame(EmptyOptional::instance(), EmptyOptional::instance()->prop);
        $this->assertFalse(isset(EmptyOptional::instance()->prop));

        EmptyOptional::instance()->prop = 123;
    }

    /**
     *
     */
    public function test_stream()
    {
        $this->assertInstanceOf(EmptyStream::class, EmptyOptional::instance()->stream());
        $this->assertSame(EmptyOptional::instance(), EmptyOptional::instance()->stream()->first());
    }

    /**
     *
     */
    public function test_get()
    {
        $this->assertNull(EmptyOptional::instance()->get());
    }
}
