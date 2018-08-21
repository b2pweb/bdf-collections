<?php

namespace Bdf\Collection\Stream\Collector;

use Bdf\Collection\HashTable;
use Bdf\Collection\Stream\ArrayStream;
use Bdf\Collection\Util\Functor\Transformer\Getter;
use Person;
use PHPUnit\Framework\TestCase;

/**
 * Class IndexingByTest
 */
class IndexingByTest extends TestCase
{
    /**
     *
     */
    public function test_collect()
    {
        $stream = new ArrayStream([
            new Person('Donald', 'Duck'),
            new Person('Mickey', 'Mouse'),
            new Person('John', 'Doe'),
            new Person('John', 'Smith'),
        ]);

        $table = $stream->collect(new IndexingBy(new Getter('firstName')));

        $this->assertCount(3, $table);
        $this->assertEquals([
            'Donald' => new Person('Donald', 'Duck'),
            'Mickey' => new Person('Mickey', 'Mouse'),
            'John'   => new Person('John', 'Smith'),
        ], $table);
    }

    /**
     *
     */
    public function test_collect_with_complex_key()
    {
        $stream = new ArrayStream([
            new Person('Donald', 'Duck'),
            new Person('Mickey', 'Mouse'),
            new Person('John', 'Doe'),
            new Person('John', 'Smith'),
        ]);

        $table = $stream->collect(new IndexingBy(
            function (Person $person) { return [$person->firstName, $person->lastName]; },
            new HashTable()
        ));

        $this->assertInstanceOf(HashTable::class, $table);
        $this->assertCount(4, $table);

        $this->assertEquals(new Person('Donald', 'Duck'), $table[['Donald', 'Duck']]);
        $this->assertEquals(new Person('Mickey', 'Mouse'), $table[['Mickey', 'Mouse']]);
        $this->assertEquals(new Person('John', 'Doe'), $table[['John', 'Doe']]);
        $this->assertEquals(new Person('John', 'Smith'), $table[['John', 'Smith']]);
    }

    /**
     *
     */
    public function test_scalar()
    {
        $stream = new ArrayStream([
            new Person('Donald', 'Duck'),
            new Person('Mickey', 'Mouse'),
            new Person('John', 'Doe'),
            new Person('John', 'Smith'),
        ]);

        $table = $stream->collect(IndexingBy::scalar('firstName'));

        $this->assertCount(3, $table);
        $this->assertEquals([
            'Donald' => new Person('Donald', 'Duck'),
            'Mickey' => new Person('Mickey', 'Mouse'),
            'John'   => new Person('John', 'Smith'),
        ], $table);

        $this->assertEquals(IndexingBy::scalar('firstName'), IndexingBy::scalar(new Getter('firstName')));
    }

    /**
     *
     */
    public function test_hash()
    {
        $stream = new ArrayStream([
            new Person('Donald', 'Duck'),
            new Person('Mickey', 'Mouse'),
            new Person('John', 'Doe'),
            new Person('John', 'Smith'),
        ]);

        $table = $stream->collect(IndexingBy::hash(function (Person $person) { return [$person->firstName, $person->lastName]; }));

        $this->assertInstanceOf(HashTable::class, $table);
        $this->assertCount(4, $table);

        $this->assertEquals(new Person('Donald', 'Duck'), $table[['Donald', 'Duck']]);
        $this->assertEquals(new Person('Mickey', 'Mouse'), $table[['Mickey', 'Mouse']]);
        $this->assertEquals(new Person('John', 'Doe'), $table[['John', 'Doe']]);
        $this->assertEquals(new Person('John', 'Smith'), $table[['John', 'Smith']]);
    }
}
