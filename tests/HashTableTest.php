<?php

namespace Bdf\Collection;

use PHPUnit\Framework\TestCase;

/**
 * Class HashTableTest
 */
class HashTableTest extends TestCase
{
    /**
     *
     */
    public function test_set_get_scalar_key()
    {
        $table = new HashTable();

        $table->set('foo', 'bar');
        $this->assertTrue($table->hasKey('foo'));
        $this->assertSame('bar', $table->get('foo'));

        $this->assertTrue($table->unset('foo'));
        $this->assertFalse($table->hasKey('foo'));
        $this->assertFalse($table->unset('foo'));
    }

    /**
     *
     */
    public function test_set_get_object_key()
    {
        $table = new HashTable();

        $key = (object) ['id' => 123];

        $table->set($key, 'bar');
        $this->assertTrue($table->hasKey($key));
        $this->assertFalse($table->hasKey((object) ['id' => 456]));
        $this->assertSame('bar', $table->get($key));

        $this->assertTrue($table->unset($key));
        $this->assertFalse($table->hasKey($key));
        $this->assertFalse($table->unset($key));
    }

    /**
     *
     */
    public function test_set_get_array_key()
    {
        $table = new HashTable();

        $key = ['id' => 123];

        $table->set($key, 'bar');
        $this->assertTrue($table->hasKey($key));
        $this->assertFalse($table->hasKey(['id' => 456]));
        $this->assertSame('bar', $table->get($key));

        $this->assertTrue($table->unset($key));
        $this->assertFalse($table->hasKey($key));
        $this->assertFalse($table->unset($key));
    }

    /**
     *
     */
    public function test_set_get_custom_hash_function_scalar()
    {
        $table = new HashTable('strtolower');

        $table->set('foo', 'bar');
        $this->assertTrue($table->hasKey('FOO'));
        $this->assertSame('bar', $table->get('Foo'));

        $this->assertTrue($table->unset('FOO'));
        $this->assertFalse($table->hasKey('foo'));
        $this->assertFalse($table->unset('foo'));
    }

    /**
     *
     */
    public function test_set_get_custom_hash_function_array()
    {
        $table = new HashTable(function ($key) { return (string) $key[0]; });

        $table->set([123, 456], 'bar');

        $this->assertTrue($table->hasKey([123, 456]));
        $this->assertTrue($table->hasKey([123, 789]));
        $this->assertTrue($table->hasKey([123]));
        $this->assertFalse($table->hasKey([456]));
        $this->assertSame('bar', $table->get([123, 789]));

        $this->assertTrue($table->unset([123]));
        $this->assertFalse($table->hasKey([123]));
        $this->assertFalse($table->unset([123]));
    }

    /**
     *
     */
    public function test_get_not_found()
    {
        $this->expectException(\OutOfBoundsException::class);

        (new HashTable())->get('not_found');
    }

    /**
     *
     */
    public function test_remove()
    {
        $table = new HashTable();

        $this->assertFalse($table->remove('bar'));

        $table->set('foo', 'bar');
        $this->assertTrue($table->remove('bar'));
        $this->assertFalse($table->hasKey('foo'));
        $this->assertFalse($table->contains('bar'));

        $table->set('value', 42);

        $this->assertFalse($table->remove('42', true));
        $this->assertTrue($table->hasKey('value'));

        $this->assertTrue($table->remove(42, true));
        $this->assertFalse($table->hasKey('value'));
    }

    /**
     *
     */
    public function test_add()
    {
        $this->expectException(\BadMethodCallException::class);

        $table = new HashTable();

        $table->add('');
    }

    /**
     *
     */
    public function test_addAll()
    {
        $this->expectException(\BadMethodCallException::class);

        $table = new HashTable();

        $table->addAll([]);
    }

    /**
     *
     */
    public function test_contains()
    {
        $table = new HashTable();

        $this->assertFalse($table->contains(42));

        $table->set('value', 42);
        $this->assertTrue($table->contains(42));
        $this->assertTrue($table->contains('42'));
        $this->assertFalse($table->contains('42', true));
    }

    /**
     *
     */
    public function test_keys_values()
    {
        $table = new HashTable();

        $table->set('foo', 'bar');
        $table->set('value', 42);

        $this->assertSame(['foo', 'value'], $table->keys());
        $this->assertSame(['bar', 42], $table->values());
    }

    /**
     *
     */
    public function test_clear()
    {
        $table = new HashTable();

        $table->set('foo', 'bar');
        $table->set('value', 42);

        $table->clear();

        $this->assertFalse($table->hasKey('foo'));
        $this->assertFalse($table->hasKey('value'));
        $this->assertTrue($table->empty());
    }

    /**
     *
     */
    public function test_empty_count()
    {
        $table = new HashTable();

        $this->assertCount(0, $table);
        $this->assertTrue($table->empty());

        $table->set('foo', 'bar');
        $table->set('value', 42);

        $this->assertCount(2, $table);
        $this->assertFalse($table->empty());
    }

    /**
     *
     */
    public function test_toArray()
    {
        $table = new HashTable();

        $table->set('foo', 'bar');
        $table->set('value', 42);

        $this->assertEquals([
            'foo' => 'bar',
            'value' => 42
        ], $table->toArray());

        $this->assertEquals([
            ['foo', 'bar'],
            ['value', 42],
        ], $table->toArray(false));
    }

    /**
     *
     */
    public function test_forEach()
    {
        $table = new HashTable();

        $table->set('foo', 'bar');
        $table->set('value', 42);

        $calls = [];

        $table->forEach(function (...$parameters) use(&$calls) {
            $calls[] = $parameters;
        });


        $this->assertEquals([
            ['bar', 'foo'],
            [42, 'value'],
        ], $calls);
    }

    /**
     *
     */
    public function test_ArrayAccess()
    {
        $table = new HashTable();

        $table[['id' => 123]] = 'John';
        $this->assertTrue(isset($table[['id' => 123]]));
        $this->assertFalse(isset($table[['id' => 456]]));
        $this->assertSame('John', $table[['id' => 123]]);

        unset($table[['id' => 123]]);
        $this->assertFalse(isset($table[['id' => 123]]));
    }

    /**
     *
     */
    public function test_array_append_operator()
    {
        $this->expectException(\BadMethodCallException::class);

        $table = new HashTable();

        $table[] = 123;
    }

    /**
     *
     */
    public function test_iterator()
    {
        $table = new HashTable();

        $table->set(['id' => 123], 'John');
        $table->set(['id' => 456], 'Donald');
        $table->set(['id' => 789], 'Mickey');

        $count = 0;

        foreach ($table as $key => $value) {
            switch ($count++) {
                case 0:
                    $this->assertSame(['id' => 123], $key);
                    $this->assertSame('John', $value);
                    continue;

                case 1:
                    $this->assertSame(['id' => 456], $key);
                    $this->assertSame('Donald', $value);
                    continue;

                case 2:
                    $this->assertSame(['id' => 789], $key);
                    $this->assertSame('Mickey', $value);
                    continue;

                default:
                    $this->fail('Unexpected count');
            }
        }
    }

    /**
     *
     */
    public function test_stream()
    {
        $table = new HashTable();

        $table->set(['id' => 123], 'John');
        $table->set(['id' => 456], 'Donald');
        $table->set(['id' => 789], 'Mickey');

        $count = 0;

        foreach (
            $table->stream()->map(function ($value) { return strtolower($value); })
            as $key => $value
        ) {
            switch ($count++) {
                case 0:
                    $this->assertSame(['id' => 123], $key);
                    $this->assertSame('john', $value);
                    continue;

                case 1:
                    $this->assertSame(['id' => 456], $key);
                    $this->assertSame('donald', $value);
                    continue;

                case 2:
                    $this->assertSame(['id' => 789], $key);
                    $this->assertSame('mickey', $value);
                    continue;

                default:
                    $this->fail('Unexpected count');
            }
        }
    }

    /**
     *
     */
    public function test_array_append_on_item()
    {
        $table = new HashTable();

        $table[123] = [];
        $table[123][] = 'Hello';
        $table[123][] = 'World';

        $this->assertSame(['Hello', 'World'], $table[123]);
    }

    /**
     *
     */
    public function test_replace()
    {
        $table = new HashTable();

        $table[123] = 'Hello';
        $table[456] = 'World';

        $this->assertTrue($table->replace([
            'a' => 'b',
            'c' => 'd'
        ]));

        $this->assertSame([
            'a' => 'b',
            'c' => 'd'
        ], $table->toArray());
    }

    /**
     *
     */
    public function test_replace_with_traversable()
    {
        $table = new HashTable();

        $table[123] = 'Hello';
        $table[456] = 'World';

        $this->assertTrue($table->replace(new ArrayCollection([
            'a' => 'b',
            'c' => 'd'
        ])));

        $this->assertSame([
            'a' => 'b',
            'c' => 'd'
        ], $table->toArray());
    }
}
