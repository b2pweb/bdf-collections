<?php

namespace Bdf\Collection\Util\Extension;

use Bdf\Collection\ArrayCollection;
use Bdf\Collection\CollectionInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class CollectionDelegationTraitTest
 */
class CollectionDelegationTraitTest extends TestCase
{
    /**
     *
     */
    public function test_add()
    {
        $inner = new ArrayCollection();
        $delegated = new MyDelegatedCollection($inner);

        $this->assertTrue($delegated->add('foo'));

        $this->assertEquals(['foo'], $inner->toArray());
    }

    /**
     *
     */
    public function test_addAll()
    {
        $inner = new ArrayCollection();
        $delegated = new MyDelegatedCollection($inner);

        $this->assertTrue($delegated->addAll(['foo', 'bar']));

        $this->assertEquals(['foo', 'bar'], $inner->toArray());
    }

    /**
     *
     */
    public function test_replace()
    {
        $inner = new ArrayCollection([1, 2]);
        $delegated = new MyDelegatedCollection($inner);

        $this->assertTrue($delegated->replace(['foo', 'bar']));

        $this->assertEquals(['foo', 'bar'], $inner->toArray());
    }

    /**
     *
     */
    public function test_remove()
    {
        $inner = new ArrayCollection(['foo', 'bar']);
        $delegated = new MyDelegatedCollection($inner);

        $this->assertFalse($delegated->remove(123));
        $this->assertTrue($delegated->remove('foo'));

        $this->assertEquals([1 => 'bar'], $inner->toArray());
    }

    /**
     *
     */
    public function test_clear()
    {
        $inner = new ArrayCollection(['foo', 'bar']);
        $delegated = new MyDelegatedCollection($inner);

        $delegated->clear();

        $this->assertEquals([], $inner->toArray());
    }

    /**
     *
     */
    public function test_empty()
    {
        $inner = new ArrayCollection(['foo', 'bar']);
        $delegated = new MyDelegatedCollection($inner);

        $this->assertFalse($delegated->empty());

        $inner->clear();
        $this->assertTrue($delegated->empty());
    }

    /**
     *
     */
    public function test_contains()
    {
        $inner = new ArrayCollection(['foo', 'bar']);
        $delegated = new MyDelegatedCollection($inner);

        $this->assertTrue($delegated->contains('foo'));
        $this->assertFalse($delegated->contains(123));
    }

    /**
     *
     */
    public function test_toArray()
    {
        $inner = new ArrayCollection(['foo', 'bar']);
        $delegated = new MyDelegatedCollection($inner);

        $this->assertEquals(['foo', 'bar'], $delegated->toArray());
    }

    /**
     *
     */
    public function test_count()
    {
        $inner = new ArrayCollection(['foo', 'bar']);
        $delegated = new MyDelegatedCollection($inner);

        $this->assertCount(2, $delegated);
    }

    /**
     *
     */
    public function test_iterator()
    {
        $inner = new ArrayCollection(['foo', 'bar']);
        $delegated = new MyDelegatedCollection($inner);

        $this->assertEquals(['foo', 'bar'], iterator_to_array($delegated));
    }

    /**
     *
     */
    public function test_stream()
    {
        $inner = new ArrayCollection(['foo', 'bar']);
        $delegated = new MyDelegatedCollection($inner);

        $this->assertEquals($inner->stream(), $delegated->stream());
    }

    /**
     *
     */
    public function test_forEach()
    {
        $inner = new ArrayCollection(['foo', 'bar']);
        $delegated = new MyDelegatedCollection($inner);

        $calls = [];

        $delegated->forEach(function (...$p) use(&$calls) { $calls[] = $p; });

        $this->assertEquals([
            ['foo', 0],
            ['bar', 1],
        ], $calls);
    }
}

class MyDelegatedCollection implements CollectionInterface
{
    use CollectionDelegationTrait;

    public function __construct(CollectionInterface $collection)
    {
        $this->setCollection($collection);
    }
}
