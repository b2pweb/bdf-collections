<?php

namespace Bdf\Collection;

use Bdf\Collection\Util\Hashable;
use PHPUnit\Framework\TestCase;

/**
 * Class HashSetTest
 */
class HashSetTest extends TestCase
{
    /**
     *
     */
    public function test_add()
    {
        $set = new HashSet();

        $this->assertTrue($set->add(123));
        $this->assertTrue($set->contains(123));
        $this->assertEquals([123], $set->toArray());
        $this->assertCount(1, $set);

        $this->assertFalse($set->add(123));
        $this->assertCount(1, $set);

        $this->assertTrue($set->add(456));
        $this->assertEquals([123, 456], $set->toArray());
        $this->assertCount(2, $set);
    }

    /**
     *
     */
    public function test_add_remove_object_with_custom_hash()
    {
        $set = new HashSet();

        $john = new CustomHash('John', 'Doe');
        $mickey = new CustomHash('Mickey', 'Mouse');
        $donald = new CustomHash('Donald', 'Duck');

        $this->assertTrue($set->add($john));
        $this->assertTrue($set->add($mickey));
        $this->assertTrue($set->add($donald));

        $this->assertTrue($set->contains($john));
        $this->assertTrue($set->contains($mickey));
        $this->assertTrue($set->contains($donald));

        $this->assertCount(3, $set);
        $this->assertEquals([$john, $mickey, $donald], $set->toArray());

        $this->assertFalse($set->add($john));
        $this->assertFalse($set->add($mickey));
        $this->assertFalse($set->add($donald));

        $this->assertTrue($set->remove($john));
        $this->assertFalse($set->contains($john));
        $this->assertCount(2, $set);
        $this->assertEquals([$mickey, $donald], $set->toArray());

        $this->assertTrue($set->add($john));
    }

    /**
     *
     */
    public function test_contains_with_objects()
    {
        $set = new HashSet();

        $this->assertFalse($set->contains(new CustomHash('John', 'Doe')));

        $set->add(new CustomHash('John', 'Doe'));

        $this->assertTrue($set->contains(new CustomHash('John', 'Doe')));
        $this->assertFalse($set->contains(new CustomHash('John', 'Smith')));

        $set = new HashSet(function (CustomHash $e) { return $e->firstName; });
        $set->add(new CustomHash('John', 'Doe'));

        $this->assertTrue($set->contains(new CustomHash('John', 'Doe')));
        $this->assertTrue($set->contains(new CustomHash('John', 'Smith')));
    }

    /**
     *
     */
    public function test_custom_hash_function()
    {
        $set = new HashSet(function ($element) {
            sort($element);

            return serialize($element);
        });

        $set->add([1, 2]);

        $this->assertTrue($set->contains([1, 2]));
        $this->assertTrue($set->contains([2, 1]));

        $set->add([4, 3]);
        $this->assertTrue($set->contains([3, 4]));
        $this->assertTrue($set->contains([4, 3]));
    }

    /**
     *
     */
    public function test_remove_no_set()
    {
        $set = new HashSet();

        $this->assertFalse($set->remove(3));

        $set->add(3);
        $this->assertTrue($set->remove(3));
    }

    /**
     *
     */
    public function test_clear()
    {
        $set = new HashSet();

        $set->add(1);
        $set->add(2);
        $set->add(3);

        $set->clear();

        $this->assertTrue($set->empty());
        $this->assertEquals([], $set->toArray());
    }

    /**
     *
     */
    public function test_forEach()
    {
        $set = new HashSet();

        $set->add(1);
        $set->add(2);
        $set->add(3);

        $callParameters = [];

        $set->forEach(function (...$parameters) use(&$callParameters) {
            $callParameters[] = $parameters;
        });

        $this->assertEquals([[1], [2], [3]], $callParameters);
    }

    /**
     *
     */
    public function test_iterator()
    {
        $set = new HashSet();

        $set->add(1);
        $set->add(2);
        $set->add(3);

        $this->assertEquals([1, 2, 3], iterator_to_array($set));
    }

    /**
     *
     */
    public function test_stream()
    {
        $set = new HashSet();

        $set->add(1);
        $set->add(2);
        $set->add(3);

        $this->assertEquals([1, 4, 9], $set->stream()->map(function ($e) { return $e * $e; })->toArray());
    }

    /**
     *
     */
    public function test_addAll_with_array()
    {
        $set = new HashSet();

        $set->add(1);
        $set->add(2);
        $set->add(3);

        $this->assertTrue($set->addAll([4, 5, 6]));
        $this->assertEquals([1, 2, 3, 4, 5, 6], $set->toArray());
    }

    /**
     *
     */
    public function test_addAll_with_Traversable()
    {
        $set = new HashSet();

        $set->add(1);
        $set->add(2);
        $set->add(3);

        $this->assertTrue($set->addAll(new ArrayCollection([4, 5, 6])));
        $this->assertEquals([1, 2, 3, 4, 5, 6], $set->toArray());
    }

    /**
     *
     */
    public function test_addAll_with_duplicate()
    {
        $set = new HashSet();

        $set->add(1);
        $set->add(2);
        $set->add(3);

        $this->assertFalse($set->addAll([4, 2, 6]));
        $this->assertEquals([1, 2, 3, 4, 6], $set->toArray());
    }

    /**
     *
     */
    public function test_replace_with_array()
    {
        $set = new HashSet();

        $set->add(1);
        $set->add(2);
        $set->add(3);

        $this->assertTrue($set->replace([4, 5, 6]));
        $this->assertEquals([4, 5, 6], $set->toArray());
    }

    /**
     *
     */
    public function test_replace_with_Traversable()
    {
        $set = new HashSet();

        $set->add(1);
        $set->add(2);
        $set->add(3);

        $this->assertTrue($set->replace(new ArrayCollection([4, 5, 6])));
        $this->assertEquals([4, 5, 6], $set->toArray());
    }

    /**
     *
     */
    public function test_replace_with_duplicate()
    {
        $set = new HashSet();

        $set->add(1);
        $set->add(2);
        $set->add(3);

        $this->assertFalse($set->replace([4, 4, 6]));
        $this->assertEquals([4, 6], $set->toArray());
    }

    /**
     *
     */
    public function test_lookup()
    {
        $set = new HashSet();

        $set->add($o = new CustomHash('John', 'Doe'));

        $this->assertSame($o, $set->lookup($o)->get());
        $this->assertSame($o, $set->lookup(new CustomHash('John', 'Doe'))->get());
        $this->assertFalse($set->lookup(new CustomHash('Donald', 'Duck'))->present());
    }

    /**
     *
     */
    public function test_spl()
    {
        $this->assertEquals(new HashSet('spl_object_hash'), HashSet::spl());

        $set = HashSet::spl();

        $o1 = new \stdClass();
        $o2 = new \stdClass();

        $set->add($o1);

        $this->assertTrue($set->contains($o1));
        $this->assertFalse($set->contains($o2));

        $this->assertTrue($set->add($o2));
        $this->assertTrue($set->contains($o1));
        $this->assertTrue($set->contains($o2));
    }
}

class CustomHash implements Hashable
{
    public $firstName;
    public $lastName;

    public function __construct($firstName, $lastName)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function hash()
    {
        return $this->lastName.' '.$this->firstName;
    }
}
