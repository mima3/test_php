<?php
class Address {
    public string $city;
    public function __construct(string $cityName)
    {
        $this->city = $cityName;
    }
}

class User {
    public Address $address;
    public string $name;

    public function __construct(string $name, string $cityName)
    {
        $this->name = $name;
        $this->address = new Address($cityName);
    }
    /*
    public function __clone()
    {
        $this->address = clone $this->address; // ここを自分で書く
    }
    */
}

$a = new User('Alice', 'Tokyo');
$b = clone $a;

$b->name = "Joe";
$b->address->city = "Kyoto";

echo '$a: id:' . spl_object_id($a) . " name:" . $a->name . " address.city:" . $a->address->city . PHP_EOL;
echo '$b: id:' . spl_object_id($b) . " name:" . $b->name . " address.city:" . $b->address->city . PHP_EOL;

echo "============================\n";

class UserDeepCopy extends User {
    public function __clone()
    {
        $this->address = clone $this->address; // ここを自分で書く
    }
}

$a = new UserDeepCopy('Alice', 'Tokyo');
$b = clone $a;

$b->name = "Joe";
$b->address->city = "Kyoto";

echo '$a: id:' . spl_object_id($a) . " name:" . $a->name . " address.city:" . $a->address->city . PHP_EOL;
echo '$b: id:' . spl_object_id($b) . " name:" . $b->name . " address.city:" . $b->address->city . PHP_EOL;
