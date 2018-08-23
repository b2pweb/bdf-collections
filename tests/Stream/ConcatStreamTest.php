<?php

namespace Bdf\Collection\Stream;

use Bdf\Collection\Stream\Accumulator\Accumulators;
use Bdf\Collection\Stream\Collector\Joining;
use Bdf\Collection\Util\Optional;
use PHPUnit\Framework\TestCase;

/**
 * Class ConcatStreamTest
 */
class ConcatStreamTest extends TestCase
{
    /**
     *
     */
    public function test_with_keys()
    {
        $stream = new ConcatStream([
            new ArrayStream([
                'foo' => 'bar',
                'value' => 42
            ]),
            new ArrayStream([
                'baz' => 'foo',
                'other' => 77
            ])
        ]);

        $this->assertSame([
            'foo'   => 'bar',
            'value' => 42,
            'baz'   => 'foo',
            'other' => 77,
        ], $stream->toArray());

        $this->assertSame([
            'foo'   => 'bar',
            'value' => 42,
            'baz'   => 'foo',
            'other' => 77,
        ], iterator_to_array($stream));
    }

    /**
     *
     */
    public function test_no_preserve_keys()
    {
        $stream = new ConcatStream([
            new ArrayStream([
                'foo' => 'bar',
                'value' => 42
            ]),
            new ArrayStream([
                'baz' => 'foo',
                'other' => 77
            ])
        ], false);

        $this->assertSame(['bar', 42, 'foo', 77], $stream->toArray());
        $this->assertSame(['bar', 42, 'foo', 77], iterator_to_array($stream));
    }

    /**
     *
     */
    public function test_concat()
    {
        $stream = new ConcatStream([
            new ArrayStream([
                'foo' => 'bar',
                'value' => 42
            ]),
            new ArrayStream([
                'baz' => 'foo',
                'other' => 77
            ])
        ]);

        $this->assertSame($stream, $stream->concat(new ArrayStream(['oof' => 'foo', 'aze' => 14])));

        $this->assertSame([
            'foo'   => 'bar',
            'value' => 42,
            'baz'   => 'foo',
            'other' => 77,
            'oof'   => 'foo',
            'aze'   => 14,
        ], $stream->toArray());
    }

    /**
     *
     */
    public function test_concat_no_preserve_key()
    {
        $stream = new ConcatStream([
            new ArrayStream([
                'foo' => 'bar',
                'value' => 42
            ]),
            new ArrayStream([
                'baz' => 'foo',
                'other' => 77
            ])
        ]);

        $this->assertSame($stream, $stream->concat(new ArrayStream(['oof' => 'foo', 'aze' => 14]), false));
        $this->assertSame(['bar', 42, 'foo', 77, 'foo', 14], $stream->toArray());
    }

    /**
     *
     */
    public function test_concat_no_preserve_preserve_keys()
    {
        $stream = new ConcatStream([
            new ArrayStream([
                'foo' => 'bar',
                'value' => 42
            ]),
            new ArrayStream([
                'baz' => 'foo',
                'other' => 77
            ])
        ], false);

        $concat = $stream->concat(new ArrayStream(['oof' => 'foo', 'aze' => 14]));

        $this->assertInstanceOf(ConcatStream::class, $concat);
        $this->assertNotSame($stream, $concat);
        $this->assertSame(['bar', 42, 'foo', 77, 'oof' => 'foo', 'aze' => 14], $concat->toArray());
    }

    /**
     *
     */
    public function test_map()
    {
        $stream = new ConcatStream([
            new ArrayStream([
                'foo' => 'bar',
                'value' => 42,
            ]),
            new ArrayStream([
                'baz' => 'foo',
                'other' => 77
            ])
        ]);

        $this->assertSame([
            'foo' => 'BAR',
            'value' => '42',
            'baz' => 'FOO',
            'other' => '77'
        ], $stream->map(function ($e) { return strtoupper($e); })->toArray());
    }

    /**
     *
     */
    public function test_filter()
    {
        $stream = new ConcatStream([
            new ArrayStream([
                'foo' => 'bar',
                'value' => 42,
            ]),
            new ArrayStream([
                'baz' => 'foo',
                'other' => 77
            ])
        ]);

        $this->assertSame([
            'value' => 42,
            'other' => 77
        ], $stream->filter(function ($e) { return is_int($e); })->toArray());
    }

    /**
     *
     */
    public function test_distinct()
    {
        $stream = new ConcatStream([
            new ArrayStream([
                'foo' => 'bar',
                'value' => 42,
            ]),
            new ArrayStream([
                'baz' => 'foo',
                'other' => 42
            ])
        ]);

        $this->assertSame([
            'foo' => 'bar',
            'value' => 42,
            'baz' => 'foo',
        ], $stream->distinct()->toArray());
    }

    /**
     *
     */
    public function test_sort()
    {
        $stream = new ConcatStream([
            new ArrayStream([5, 3]),
            new ArrayStream([8, 2])
        ]);

        $this->assertSame([2, 3, 5, 8], $stream->sort()->toArray());
    }

    /**
     *
     */
    public function test_flatMap()
    {
        $stream = new ConcatStream([
            new ArrayStream([[5, 2], [3, 4]]),
            new ArrayStream([[8, 5], [2, 1]])
        ]);

        $this->assertSame([5, 2, 3, 4, 8, 5, 2, 1], $stream->flatMap(function ($e) { return $e; })->toArray());
    }

    /**
     *
     */
    public function test_forEach()
    {
        $stream = new ConcatStream([
            new ArrayStream([
                'foo' => 'bar',
                'value' => 42,
            ]),
            new ArrayStream([
                'baz' => 'foo',
                'other' => 14
            ])
        ]);

        $calls = [];
        $stream->forEach(function (...$p) use(&$calls) { $calls[] = $p; });

        $this->assertSame([
            ['bar', 'foo'],
            [42, 'value'],
            ['foo', 'baz'],
            [14, 'other']
        ], $calls);
    }

    /**
     *
     */
    public function test_first()
    {
        $stream = new ConcatStream([
            new ArrayStream([
                'foo' => 'bar',
                'value' => 42,
            ]),
            new ArrayStream([
                'baz' => 'foo',
                'other' => 14
            ])
        ]);

        $this->assertEquals(Optional::of('bar'), $stream->first());
        $this->assertEquals(Optional::empty(), (new ConcatStream([]))->first());
    }

    /**
     *
     */
    public function test_reduce()
    {
        $stream = new ConcatStream([
            new ArrayStream([5, 3]),
            new ArrayStream([8, 2])
        ]);

        $this->assertSame(18, $stream->reduce(Accumulators::sum()));
    }

    /**
     *
     */
    public function test_collect()
    {
        $stream = new ConcatStream([
            new ArrayStream([5, 3]),
            new ArrayStream([8, 2])
        ]);

        $this->assertSame('5,3,8,2', $stream->collect(new Joining(',')));
    }

    /**
     *
     */
    public function test_matchAll()
    {
        $stream = new ConcatStream([
            new ArrayStream([5, 3]),
            new ArrayStream([8, 2])
        ]);

        $this->assertTrue($stream->matchAll(function ($e) { return $e < 10; }));
        $this->assertFalse($stream->matchAll(function ($e) { return $e % 2 === 0; }));
    }

    /**
     *
     */
    public function test_matchOne()
    {
        $stream = new ConcatStream([
            new ArrayStream([5, 3]),
            new ArrayStream([8, 2])
        ]);

        $this->assertTrue($stream->matchOne(function ($e) { return $e % 2 === 0; }));
        $this->assertFalse($stream->matchOne(function ($e) { return $e > 10; }));
    }
}
