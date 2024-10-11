<?php

use Bdf\Collection\Util\Hashable;

class Person
{
    public $firstName;
    public $lastName;
    public $type;

    public function __construct($firstName, $lastName, ?PersonType $type = null)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->type = $type;
    }

    public function firstName()
    {
        return $this->firstName;
    }

    public function lastName()
    {
        return $this->lastName;
    }

    public function type()
    {
        return $this->type;
    }
}

class PersonType implements Hashable
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function hash()
    {
        return strtoupper($this->name);
    }

    public static function human()
    {
        return new self('HUMAN');
    }

    public static function animal()
    {
        return new self('ANIMAL');
    }
}
