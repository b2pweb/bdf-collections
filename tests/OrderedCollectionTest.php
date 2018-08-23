<?php

namespace Bdf\Collection;

use PHPUnit\Framework\TestCase;

/**
 * Class OrderedCollectionTest
 */
class OrderedCollectionTest extends TestCase
{
    /**
     *
     */
    public function test_custom_comparator()
    {
        $collection = new OrderedCollection(function ($a, $b) { return [$a%2, $a] <=> [$b%2, $b]; });
        $collection->replace([4, 1, 8, 7]);

        $this->assertEquals([4, 8, 1, 7], $collection->toArray());
    }

    /**
     *
     */
    public function test_add_contains()
    {
        $collection = new OrderedCollection();

        $this->assertTrue($collection->add(5));
        $this->assertTrue($collection->add(9));
        $this->assertTrue($collection->add(2));

        $this->assertTrue($collection->contains(5));
        $this->assertTrue($collection->contains(9));
        $this->assertTrue($collection->contains(2));
    }

    /**
     *
     */
    public function test_add_1_already_sorted()
    {
        $collection = new OrderedCollection();

        $this->assertTrue($collection->add(1));

        $this->assertEquals([1], $collection->toArray());
    }

    /**
     *
     */
    public function test_toArray()
    {
        $collection = new OrderedCollection();

        $collection->add(5);
        $collection->add(9);
        $collection->add(2);

        $this->assertSame([2, 5, 9], $collection->toArray());
    }

    /**
     *
     */
    public function test_iterator()
    {
        $collection = new OrderedCollection();

        $collection->add(5);
        $collection->add(9);
        $collection->add(2);

        $this->assertSame([2, 5, 9], iterator_to_array($collection));
    }

    /**
     *
     */
    public function test_addAll_array()
    {
        $collection = new OrderedCollection();

        $collection->add(3);

        $this->assertTrue($collection->addAll([5, 9, 2]));

        $this->assertSame([2, 3, 5, 9], iterator_to_array($collection));
    }

    /**
     *
     */
    public function test_addAll_iterable()
    {
        $collection = new OrderedCollection();

        $collection->add(3);

        $this->assertTrue($collection->addAll(new ArrayCollection([5, 9, 2])));

        $this->assertSame([2, 3, 5, 9], iterator_to_array($collection));
    }

    /**
     *
     */
    public function test_replace_array()
    {
        $collection = new OrderedCollection();

        $collection->add(3);

        $this->assertTrue($collection->replace([5, 9, 2]));

        $this->assertSame([2, 5, 9], iterator_to_array($collection));
    }

    /**
     *
     */
    public function test_replace_iterable()
    {
        $collection = new OrderedCollection();

        $collection->add(3);

        $this->assertTrue($collection->replace(new ArrayCollection([5, 9, 2])));

        $this->assertSame([2, 5, 9], iterator_to_array($collection));
    }

    /**
     *
     */
    public function test_contains_strict()
    {
        $collection = new OrderedCollection();
        $collection->replace([5, 9, 2]);

        $this->assertTrue($collection->contains('5'));
        $this->assertFalse($collection->contains('5', true));
    }

    /**
     *
     */
    public function test_remove()
    {
        $collection = new OrderedCollection();
        $collection->replace([5, 9, 2]);

        $this->assertFalse($collection->remove('not_found'));
        $this->assertTrue($collection->remove(5));

        $this->assertSame([2, 9], $collection->toArray());

        $this->assertFalse($collection->remove(5));
        $this->assertFalse($collection->remove('9', true));

        $this->assertSame([2, 9], $collection->toArray());
    }

    /**
     *
     */
    public function test_clear()
    {
        $collection = new OrderedCollection();
        $collection->replace([5, 9, 2]);

        $collection->clear();

        $this->assertSame([], $collection->toArray());
    }

    /**
     *
     */
    public function test_count_empty()
    {
        $collection = new OrderedCollection();

        $this->assertTrue($collection->empty());
        $this->assertCount(0, $collection);

        $collection->replace([4, 1, 5]);

        $this->assertFalse($collection->empty());
        $this->assertCount(3, $collection);
    }

    /**
     *
     */
    public function test_forEach()
    {
        $collection = new OrderedCollection();
        $collection->replace([4, 1, 5]);

        $calls = [];
        $collection->forEach(function (...$p) use(&$calls) { $calls[] = $p; });

        $this->assertEquals([
            [1, 0],
            [4, 1],
            [5, 2],
        ], $calls);
    }

    /**
     *
     */
    public function test_stream()
    {
        $collection = new OrderedCollection();
        $collection->replace([4, 1, 5]);

        $this->assertEquals([2, 8, 10], $collection->stream()->map(function ($e) { return $e * 2; })->toArray());
    }

    /**
     *
     */
    public function test_at_invalid_position()
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Invalid position 155');

        (new OrderedCollection())->at(155);
    }

    /**
     *
     */
    public function test_at()
    {
        $collection = new OrderedCollection();
        $collection->replace([7, 1, 5]);

        $this->assertEquals(5, $collection->at(1));
    }

    /**
     *
     */
    public function test_ArrayAccess()
    {
        $collection = new OrderedCollection();
        $collection->replace([7, 1, 5]);

        $this->assertEquals(1, $collection[0]);
        $this->assertEquals(5, $collection[1]);
        $this->assertEquals(7, $collection[2]);

        $this->assertTrue(isset($collection[0]));
        $this->assertFalse(isset($collection[155]));

        unset($collection[1]);
        $this->assertCount(2, $collection);
        $this->assertEquals([1, 7], $collection->toArray());
        $this->assertEquals(1, $collection[0]);
        $this->assertEquals(7, $collection[1]);

        $collection[] = 3;
        $this->assertCount(3, $collection);
        $this->assertEquals([1, 3, 7], $collection->toArray());
    }

    /**
     *
     */
    public function test_offsetSet()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot set a value into an OrderedCollection');

        $collection = new OrderedCollection();
        $collection[5] = 3;
    }

    /**
     *
     */
    public function test_search()
    {
        $collection = new OrderedCollection();
        $collection->replace([7, 1, 5]);

        $this->assertEquals(0, $collection->search(1));
        $this->assertEquals(1, $collection->search(5));
        $this->assertEquals(2, $collection->search(7));

        $this->assertFalse($collection->search('not_found'));

        $this->assertEquals(1, $collection->search('5'));
        $this->assertFalse($collection->search('5', true));
    }

    /**
     *
     */
    public function test_search_big_collection()
    {
        $collection = new OrderedCollection();
        $collection->replace(range(0, 10000));

        $this->assertEquals(5, $collection->search(5));

        $this->assertFalse($collection->search(-10));

        $this->assertEquals(5, $collection->search('5'));
        $this->assertFalse($collection->search('5', true));
    }

    /**
     *
     */
    public function test_search_big_collection_with_comparator()
    {
        $collection = new OrderedCollection(function ($a, $b) { return [$a % 2, $a] <=> [$b % 2, $b]; });
        $collection->replace(range(0, 10000));

        $this->assertEquals(5003, $collection->search(5));

        $this->assertFalse($collection->search(-10));

        $this->assertEquals(5003, $collection->search('5'));
        $this->assertFalse($collection->search('5', true));
    }
}
