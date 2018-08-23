<?php

namespace Bdf\Collection\Util;

use Bdf\Collection\Stream\SingletonStream;
use PHPUnit\Framework\TestCase;

/**
 * Class OptionalTest
 */
class OptionalTest extends TestCase
{
    /**
     *
     */
    public function test_nullable()
    {
        $this->assertSame(EmptyOptional::instance(), Optional::nullable(null));
        $this->assertEquals(Optional::nullable(123), Optional::nullable(123));

        $this->assertTrue(Optional::nullable(123)->present());
        $this->assertEquals(123, Optional::nullable(123)->get());
    }

    /**
     *
     */
    public function test_empty()
    {
        $this->assertInstanceOf(EmptyOptional::class, Optional::empty());
        $this->assertSame(EmptyOptional::instance(), Optional::empty());
    }

    /**
     *
     */
    public function test_of_with_null()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('The value should not be null');

        Optional::of(null);
    }

    /**
     *
     */
    public function test_of()
    {
        $opt = Optional::of('123');

        $this->assertTrue($opt->present());
        $this->assertEquals('123', $opt->get());
    }

    /**
     *
     */
    public function test_filter()
    {
        $opt = Optional::of(123);

        $this->assertSame($opt, $opt->filter(function ($e) { return $e > 5; }));
        $this->assertSame(Optional::empty(), $opt->filter(function ($e) { return $e < 5; }));
    }

    /**
     *
     */
    public function test_map()
    {
        $opt = Optional::of(123);

        $this->assertEquals(Optional::of(246), $opt->map(function ($e) { return $e * 2; }));
        $this->assertEquals(Optional::empty(), $opt->map(function ($e) { return null; }));
    }

    /**
     *
     */
    public function test_apply()
    {
        $opt = Optional::of(123);

        $opt->apply(function ($e) use(&$param) { $param = $e; });

        $this->assertEquals(123, $param);
    }

    /**
     *
     */
    public function test_or()
    {
        $opt = Optional::of(123);

        $opt->apply(function ($e) use(&$param) { $param = $e; });

        $this->assertEquals(123, $opt->or(456));
        $this->assertEquals(123, $opt->orThrows());

        $called = false;
        $this->assertEquals(123, $opt->orSupply(function () use(&$called) { $called = true; }));
        $this->assertFalse($called);
    }

    /**
     *
     */
    public function test_magic_methods()
    {
        $opt = Optional::of(new \ReflectionClass(\ArrayObject::class));

        $this->assertEquals(Optional::of(\ArrayObject::class), $opt->name);
        $this->assertEquals(Optional::of(new \ArrayObject([1, 2, 3])), $opt->newInstance([1, 2, 3]));
        $this->assertTrue(isset($opt->name));
        $this->assertFalse(isset($opt->not_found));

        $opt = Optional::of($o = new \stdClass());

        $opt->foo = 'bar';
        $this->assertEquals('bar', $o->foo);
    }

    /**
     *
     */
    public function test_stream()
    {
        $opt = Optional::of(123);

        $this->assertEquals(new SingletonStream(123), $opt->stream());
        $this->assertEquals($opt, $opt->stream()->first());
    }
}
