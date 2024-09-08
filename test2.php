<?php

declare(strict_types=1);

class MyClass1 implements Serializable
{
    private $name;
    private $age;

    public function __construct($name, $age)
    {
        $this->name = $name;
        $this->age = $age;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getAge()
    {
        return $this->age;
    }

    public function setAge($age)
    {
        $this->age = $age;
    }

    public function serialize()
    {
        // return serialize(array(
        //     $this->name,
        //     $this->age
        // ));
        return [];
    }

    public function unserialize($data)
    {
        list(
            $this->name,
            $this->age
        ) = unserialize($data);
    }
}


$a = new MyClass1('awd', 24);


$a = $a->serialize();
