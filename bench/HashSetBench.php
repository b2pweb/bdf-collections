<?php

namespace Bdf\Collection;

use Bdf\Collection\Util\Hashable;

/**
 * @Revs(10)
 * @BeforeMethods({"initData"})
 */
class HashSetBench
{
    private $data = [];

    public function initData()
    {
        $this->data = [
            '100'    => self::intArray(100),
            '1000'   => self::intArray(1000),
            '10000'  => self::intArray(10000),
            '100000' => self::intArray(100000),
            'int'              => self::intArray(10000),
            'simpleObject'     => self::objectArray(10000, SimpleObject::class),
            'customHashObject' => self::objectArray(10000, CustomHashObject::class),
        ];

        $set = new HashSet();

        foreach ($this->data['customHashObject'] as $i) {
            $set->add($i);
        }

        $this->data['HashSet'] = $set;
    }

    /**
     * @ParamProviders("provideArrayData")
     * @Groups({"distinct"})
     */
    public function bench_HashSet_add_toArray($param)
    {
        $set = new HashSet();

        foreach ($this->data[$param['data']] as $value) {
            $set->add($value);
        }

        return $set->toArray();
    }

    /**
     * @ParamProviders("provideArrayData")
     * @Groups({"distinct"})
     */
    public function bench_array_unique($param)
    {
        return array_unique($this->data[$param['data']], SORT_REGULAR);
    }

    /**
     * @ParamProviders("provideSizes")
     * @Groups({"complexity", "HashSet"})
     */
    public function bench_HashSet_add_complexity($param)
    {
        $set = new HashSet();

        foreach ($this->data[$param['data']] as $value) {
            $set->add($value);
        }

        return $set->toArray();
    }

    /**
     * @ParamProviders("provideSizes")
     * @Groups({"complexity", "array_unique"})
     */
    public function bench_array_unique_complexity($param)
    {
        return array_unique($this->data[$param['data']], SORT_REGULAR);
    }

    /**
     * @Groups({"contains"})
     */
    public function bench_HashSet_contains()
    {
        $this->data['HashSet']->contains(new CustomHashObject(2, 3));
    }

    /**
     * @Groups({"contains"})
     */
    public function bench_array_search()
    {
        array_search(new CustomHashObject(2, 3), $this->data['customHashObject']);
    }

    public function provideArrayData()
    {
        return [
            ['data' => 'int'],
            ['data' => 'simpleObject'],
            ['data' => 'customHashObject'],
        ];
    }

    public function provideSizes()
    {
        return [
            ['data' => 100],
            ['data' => 1000],
            ['data' => 10000],
            ['data' => 100000],
        ];
    }

    private static function intArray($size)
    {
        $array = [];

        while ($size--) {
            $array[] = rand(0, 100);
        }

        return $array;
    }

    private static function objectArray($size, $className)
    {
        $array = [];

        while ($size--) {
            $array[] = new $className(rand(0, 10), rand(0, 10));
        }

        return $array;
    }
}

class SimpleObject
{
    private $a;
    private $b;

    public function __construct($a, $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
}

class CustomHashObject implements Hashable
{
    private $a;
    private $b;

    public function __construct($a, $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    public function hash()
    {
        return $this->a.':'.$this->b;
    }
}
