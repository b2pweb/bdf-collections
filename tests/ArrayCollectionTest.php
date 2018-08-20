<?php

namespace Bdf\Collection;

use PHPUnit\Framework\TestCase;

/**
 * Class ArrayCollectionTest
 */
class ArrayCollectionTest extends TestCase
{
    /**
     *
     */
    public function test_toArray()
    {
        $collection = new ArrayCollection([1, 2, 3]);

        $this->assertSame([1, 2, 3], $collection->toArray());
    }

    /**
     *
     */
    public function test_contains()
    {
        $collection = new ArrayCollection([1, 2, 3]);

        $this->assertTrue($collection->contains(1));
        $this->assertTrue($collection->contains('1'));
        $this->assertFalse($collection->contains('1', true));
        $this->assertFalse($collection->contains(456));
    }

    /**
     *
     */
    public function test_remove()
    {
        $collection = new ArrayCollection([1, 2, 3]);

        $this->assertTrue($collection->remove(2));
        $this->assertFalse($collection->contains(2));
        $this->assertEquals([0 => 1, 2 => 3], $collection->toArray());

        $this->assertFalse($collection->remove('not_found'));
        $this->assertFalse($collection->remove('1', true));
        $this->assertTrue($collection->contains(1));
    }

    /**
     *
     */
    public function test_clear()
    {
        $collection = new ArrayCollection([1, 2, 3]);

        $collection->clear();

        $this->assertCount(0, $collection);
        $this->assertTrue($collection->empty());
        $this->assertEquals([], $collection->toArray());
    }

    /**
     *
     */
    public function test_empty()
    {
        $this->assertTrue((new ArrayCollection())->empty());
        $this->assertFalse((new ArrayCollection([1]))->empty());
    }

    /**
     *
     */
    public function test_iterator()
    {
        $this->assertEquals([1, 2, 3], iterator_to_array(new ArrayCollection([1, 2, 3])));
        $this->assertEquals(['foo' => 'bar'], iterator_to_array(new ArrayCollection(['foo' => 'bar'])));
    }

    /**
     *
     */
    public function test_arrayAccess_index_array()
    {
        $collection = new ArrayCollection([1, 2, 3]);

        $this->assertSame(2, $collection[1]);
        $this->assertTrue(isset($collection[1]));
        $this->assertFalse(isset($collection[123]));

        $collection[3] = 4;
        $this->assertSame(4, $collection[3]);

        $collection[] = 5;
        $this->assertSame(5, $collection[4]);

        unset($collection[1]);
        $this->assertEquals([1, 3, 4, 5], $collection->values());
    }

    /**
     *
     */
    public function test_arrayAccess_assoc_array()
    {
        $collection = new ArrayCollection(['foo' => 'bar']);

        $this->assertSame('bar', $collection['foo']);
        $this->assertTrue(isset($collection['foo']));
        $this->assertFalse(isset($collection['not_found']));

        $collection['name'] = 'John';
        $this->assertSame('John', $collection['name']);

        unset($collection['foo']);
        $this->assertEquals(['name' => 'John'], $collection->toArray());
    }

    /**
     *
     */
    public function test_count()
    {
        $collection = new ArrayCollection([1, 2, 3]);

        $this->assertCount(3, $collection);

        $collection->add(4);
        $this->assertCount(4, $collection);
    }

    /**
     *
     */
    public function test_get_set()
    {
        $collection = new ArrayCollection([1, 2, 3]);

        $collection->set('foo', 'bar');

        $this->assertTrue($collection->contains('bar'));
        $this->assertSame('bar', $collection->get('foo'));
    }

    /**
     *
     */
    public function test_get_not_found()
    {
        $this->expectException(\OutOfBoundsException::class);

        (new ArrayCollection())->get('not found');
    }

    /**
     *
     */
    public function test_add()
    {
        $collection = new ArrayCollection();

        $collection->add(1);
        $collection->add(2);

        $this->assertEquals([1, 2], $collection->toArray());
    }

    /**
     *
     */
    public function test_hasKey()
    {
        $collection = new ArrayCollection(['foo' => 'bar']);

        $this->assertTrue($collection->hasKey('foo'));
        $this->assertFalse($collection->hasKey('not_found'));
    }

    /**
     *
     */
    public function test_unset()
    {
        $collection = new ArrayCollection(['foo' => 'bar']);

        $collection->unset('foo');
        $this->assertFalse($collection->hasKey('foo'));
        $this->assertEquals([], $collection->toArray());
    }

    /**
     *
     */
    public function test_keys_values()
    {
        $collection = new ArrayCollection([
            'foo'  => 'bar',
            'name' => 'John',
        ]);

        $this->assertEquals(['foo', 'name'], $collection->keys());
        $this->assertEquals(['bar', 'John'], $collection->values());
    }

    /**
     *
     */
    public function test_forEach()
    {
        $collection = new ArrayCollection([
            'foo'  => 'bar',
            'name' => 'John',
        ]);

        $calls = [];

        $collection->forEach(function ($e, $k) use(&$calls) {
            $calls[] = [$e, $k];
        });

        $this->assertEquals([
            ['bar', 'foo'],
            ['John', 'name'],
        ], $calls);
    }

    /**
     *
     */
    public function test_stream()
    {
        $collection = new ArrayCollection([
            'foo'  => 'bar',
            'name' => 'John',
        ]);

        $this->assertEquals([
            'foo'  => 'BAR',
            'name' => 'JOHN',
        ], $collection->stream()->map(function ($e) { return strtoupper($e); })->toArray());
    }

    /**
     *
     */
    public function test_mutableStream()
    {
        $collection = new ArrayCollection([
            'foo'  => 'bar',
            'name' => 'John',
        ]);

        $this->assertEquals([
            'foo'  => 'BAR',
            'name' => 'JOHN',
        ], $collection->mutableStream()->map(function ($e) { return strtoupper($e); })->toArray());
    }

    /**
     *
     */
    public function test_array_append_on_item()
    {
        $table = new ArrayCollection();

        $table[123] = [];
        $table[123][] = 'Hello';
        $table[123][] = 'World';

        $this->assertSame(['Hello', 'World'], $table[123]);
    }
}
