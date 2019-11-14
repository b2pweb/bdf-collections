<?php

namespace Bdf\Collection\Util;

use PHPUnit\Framework\TestCase;

/**
 * Class HashTest
 */
class HashTest extends TestCase
{
    /**
     *
     */
    public function test_hash()
    {
        ini_set('serialize_precision', 10);

        $this->assertEquals('N;', Hash::compute(null));
        $this->assertEquals('s:5:"hello";', Hash::compute('hello'));
        $this->assertEquals('i:123;', Hash::compute(123));
        $this->assertEquals('d:12.3;', Hash::compute(12.3));
        $this->assertEquals('O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}', Hash::compute((object) ['foo' => 'bar']));
        $this->assertEquals('a:1:{s:3:"foo";s:3:"bar";}', Hash::compute(['foo' => 'bar']));
        $this->assertEquals('O:my_hash', Hash::compute(new HashableObject()));
    }
}

class HashableObject implements Hashable
{
    public function hash()
    {
        return 'my_hash';
    }
}
