<?php

namespace Bdf\Collection\Stream\Collector;

use Bdf\Collection\HashTable;
use Bdf\Collection\Stream\ArrayStream;
use Bdf\Collection\Util\Functor\Transformer\Getter;
use Person;
use PHPUnit\Framework\TestCase;

/**
 * Class GroupingByTest
 */
class GroupingByTest extends TestCase
{
    /**
     *
     */
    public function test_collect()
    {
        $stream = new ArrayStream([
            'a' => new Person('Donald', 'Duck'),
            'b' => new Person('Mickey', 'Mouse'),
            'c' => new Person('John', 'Doe'),
            'd' => new Person('John', 'Smith'),
        ]);

        $table = $stream->collect(new GroupingBy(new Getter('firstName')));

        $this->assertCount(3, $table);
        $this->assertEquals([
            'Donald' => [new Person('Donald', 'Duck')],
            'Mickey' => [new Person('Mickey', 'Mouse')],
            'John'   => [new Person('John', 'Doe'), new Person('John', 'Smith')],
        ], $table);
    }

    /**
     *
     */
    public function test_collect_preserveKeys()
    {
        $stream = new ArrayStream([
            'a' => new Person('Donald', 'Duck'),
            'b' => new Person('Mickey', 'Mouse'),
            'c' => new Person('John', 'Doe'),
            'd' => new Person('John', 'Smith'),
        ]);

        $table = $stream->collect(new GroupingBy(new Getter('firstName'), true));

        $this->assertCount(3, $table);
        $this->assertEquals([
            'Donald' => ['a' => new Person('Donald', 'Duck')],
            'Mickey' => ['b' => new Person('Mickey', 'Mouse')],
            'John'   => ['c' => new Person('John', 'Doe'), 'd' => new Person('John', 'Smith')],
        ], $table);
    }

    /**
     *
     */
    public function test_collect_with_complex_key()
    {
        $stream = new ArrayStream([
            new Person('Donald', 'Duck', \PersonType::animal()),
            new Person('Mickey', 'Mouse', \PersonType::animal()),
            new Person('John', 'Doe', \PersonType::human()),
            new Person('John', 'Smith', \PersonType::human()),
        ]);

        $table = $stream->collect(GroupingBy::hash(new Getter('type')));

        $this->assertInstanceOf(HashTable::class, $table);
        $this->assertCount(2, $table);

        $this->assertEquals([new Person('Donald', 'Duck', \PersonType::animal()), new Person('Mickey', 'Mouse', \PersonType::animal())], $table[\PersonType::animal()]);
        $this->assertEquals([new Person('John', 'Doe', \PersonType::human()), new Person('John', 'Smith', \PersonType::human())], $table[\PersonType::human()]);
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

        $table = $stream->collect(GroupingBy::scalar('firstName'));

        $this->assertCount(3, $table);
        $this->assertEquals([
            'Donald' => [new Person('Donald', 'Duck')],
            'Mickey' => [new Person('Mickey', 'Mouse')],
            'John'   => [new Person('John', 'Doe'), new Person('John', 'Smith')],
        ], $table);

        $this->assertEquals(GroupingBy::scalar('firstName'), GroupingBy::scalar(new Getter('firstName')));
    }
}
