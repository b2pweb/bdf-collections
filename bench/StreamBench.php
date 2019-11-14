<?php

namespace Bdf\Collection;

use Bdf\Collection\Stream\ArrayStream;
use Bdf\Collection\Stream\MutableArrayStream;
use Bdf\Collection\Stream\Accumulator\Accumulators;
use Bdf\Collection\Util\Functor\Transformer\Getter;

/**
 * @Revs(10)
 * @Warmup(1)
 * @BeforeMethods({"initData"})
 */
class StreamBench
{
    private $data;

    public function initData()
    {
        $this->data = [];

        srand(1000);

        for ($i = 0; $i < 1000; ++$i) {
            $this->data[] = new MyEntity($i, new MyEntity(rand(0, 50)));
        }
    }

    /**
     * @Groups({"transform"})
     */
    public function bench_Stream_complex_transform()
    {
        $stream = new ArrayStream($this->data);

        return $stream
            ->filter(function ($e) { return $e->id % 3 === 0; })
            ->map(function ($e) { return $e->sub; })
            ->distinct()
            ->toArray()
        ;
    }

    /**
     * @Groups({"transform"})
     */
    public function bench_MutableArrayStream_complex_transform()
    {
        $stream = new MutableArrayStream($this->data);

        return $stream
            ->filter(function ($e) { return $e->id % 3 === 0; })
            ->map(function ($e) { return $e->sub; })
            ->distinct()
            ->toArray()
        ;
    }

    /**
     * @Groups({"transform"})
     */
    public function bench_native_array_complex_transform()
    {
        $array = $this->data;

        return array_unique(
            array_map(
                function ($e) { return $e->sub; },
                array_filter($array, function ($e) { return $e->id % 3 === 0; })
            ),
            SORT_REGULAR
        );
    }

    /**
     * @Groups({"reduce"})
     */
    public function bench_reduce_with_functor()
    {
        return (new ArrayStream(range(0, 100)))->reduce(Accumulators::sum());
    }

    /**
     * @Groups({"reduce"})
     */
    public function bench_reduce_with_closure()
    {
        return (new ArrayStream(range(0, 100)))->reduce(function ($a, $b) { return $a + $b; });
    }

    /**
     * @Groups({"map"})
     */
    public function bench_map_getter_functor()
    {
        return (new ArrayStream($this->data))->map(new Getter('sub'))->toArray();
    }

    /**
     * @Groups({"map"})
     */
    public function bench_map_getter_closure()
    {
        return (new ArrayStream($this->data))->map(function ($e) { return $e->sub(); })->toArray();
    }

    /**
     * @Groups({"sort"})
     */
    public function bench_sort_array_stream()
    {
        return iterator_to_array((new ArrayStream($this->data))->sort());
    }

    /**
     * @Groups({"sort"})
     */
    public function bench_sort_mutable_array_stream()
    {
        return iterator_to_array((new MutableArrayStream($this->data))->sort());
    }

    /**
     * @Groups({"sort"})
     */
    public function bench_sort_array_iterator()
    {
        $data = new \ArrayIterator($this->data);
        $data->asort();

        return iterator_to_array($data);
    }
}

class MyEntity implements \Bdf\Collection\Util\Hashable
{
    public $id;
    public $sub;

    public function __construct($id, $sub = null)
    {
        $this->id = $id;
        $this->sub = $sub;
    }

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return MyEntity
     */
    public function sub()
    {
        return $this->sub;
    }

    public function hash()
    {
        return $this->id;
    }
}
