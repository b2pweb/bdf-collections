<?php

namespace Bdf\Collection\Util\Extension;

use Bdf\Collection\ArrayCollection;
use Bdf\Collection\TableInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class TableDelegationTraitTest
 */
class TableDelegationTraitTest extends TestCase
{
    /**
     *
     */
    public function test_set()
    {
        $inner = new ArrayCollection();
        $delegated = new MyTableDelegate($inner);

        $delegated->set('foo', 'bar');

        $this->assertEquals(['foo' => 'bar'], $inner->toArray());
    }

    /**
     *
     */
    public function test_get()
    {
        $inner = new ArrayCollection(['foo' => 'bar']);
        $delegated = new MyTableDelegate($inner);

        $this->assertEquals('bar', $delegated->get('foo'));
    }

    /**
     *
     */
    public function test_hasKey()
    {
        $inner = new ArrayCollection(['foo' => 'bar']);
        $delegated = new MyTableDelegate($inner);

        $this->assertTrue($delegated->hasKey('foo'));
        $this->assertFalse($delegated->hasKey('not_found'));
    }

    /**
     *
     */
    public function test_unset()
    {
        $inner = new ArrayCollection(['foo' => 'bar']);
        $delegated = new MyTableDelegate($inner);

        $this->assertFalse($delegated->unset('not_found'));
        $this->assertTrue($delegated->unset('foo'));

        $this->assertEquals([], $inner->toArray());
    }

    /**
     *
     */
    public function test_keys()
    {
        $inner = new ArrayCollection(['foo' => 'bar']);
        $delegated = new MyTableDelegate($inner);

        $this->assertEquals(['foo'], $delegated->keys());
    }

    /**
     *
     */
    public function test_values()
    {
        $inner = new ArrayCollection(['foo' => 'bar']);
        $delegated = new MyTableDelegate($inner);

        $this->assertEquals(['bar'], $delegated->values());
    }

    /**
     *
     */
    public function test_ArrayAccess()
    {
        $inner = new ArrayCollection(['foo' => 'bar']);
        $delegated = new MyTableDelegate($inner);

        $this->assertFalse(isset($delegated['not_found']));
        $this->assertTrue(isset($delegated['foo']));
        $this->assertEquals('bar', $delegated['foo']);

        unset($delegated['foo']);
        $this->assertTrue($inner->empty());

        $delegated['value'] = 42;
        $this->assertEquals(['value' => 42], $inner->toArray());
    }
}

class MyTableDelegate implements TableInterface
{
    use TableDelegationTrait;

    public function __construct(TableInterface $table)
    {
        $this->setCollection($table);
    }
}
