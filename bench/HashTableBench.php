<?php

namespace Bdf\Collection;

use Bdf\Collection\Util\Hashable;

/**
 * @Revs(20)
 * @Warmup(1)
 * @BeforeMethods({"initData"})
 */
class HashTableBench
{
    /**
     * @var SimpleObject[]
     */
    private $data;

    private $table;

    private $collection;

    public function initData()
    {
        $this->data = [];
        $this->table = new HashTable();
        $this->collection = new ArrayCollection();

        srand(1000);

        for ($i = 0; $i < 10000; ++$i) {
            $this->data[] = $o = new SimpleObject(rand(0, 1000), rand(0, 1000));

            $this->table[$o->a] = $o;
            $this->collection[$o->a] = $o;
        }
    }

    /**
     * @Groups({"set"})
     */
    public function bench_HashTable_set()
    {
        $table = new HashTable();

        foreach ($this->data as $object) {
            $table[$object->a.':'.$object->b] = $object;
        }
    }

    /**
     * @Groups({"set"})
     */
    public function bench_HashTable_set_array_key()
    {
        $table = new HashTable();

        foreach ($this->data as $object) {
            $table[[$object->a, $object->b]] = $object;
        }
    }

    /**
     * @Groups({"set"})
     */
    public function bench_HashTable_set_object_key()
    {
        $table = new HashTable();

        foreach ($this->data as $object) {
            $table[$object] = $object;
        }
    }

    /**
     * @Groups({"set"})
     */
    public function bench_ArrayCollection_set()
    {
        $table = new ArrayCollection();

        foreach ($this->data as $object) {
            $table[$object->a.':'.$object->b] = $object;
        }
    }

    /**
     * @Groups({"get"})
     */
    public function bench_HashTable_has_get()
    {
        for ($i = 0; $i < 1000; ++$i) {
            if (isset($this->table[$i])) {
                $this->table[$i];
            }
        }
    }

    /**
     * @Groups({"get"})
     */
    public function bench_ArrayCollection_has_get()
    {
        for ($i = 0; $i < 1000; ++$i) {
            if (isset($this->collection[$i])) {
                $this->collection[$i];
            }
        }
    }
}

class SimpleObject implements Hashable
{
    public $a;
    public $b;

    public function __construct($a, $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    public function a()
    {
        return $this->a;
    }

    public function b()
    {
        return $this->b;
    }

    public function hash()
    {
        return $this->a.':'.$this->b;
    }
}
