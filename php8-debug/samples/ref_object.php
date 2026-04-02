<?php
class User {
    public string $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

$a = new User('Alice');
$b = $a;
$c =& $a;

$b->name = 'Bob';

var_dump($a); // name; Bob
var_dump($b); // name; Bob
var_dump($c); // name; Bob

$a = new User('Jack');
var_dump($a); // name; Jack
var_dump($b); // name: Bob
var_dump($c); // name; Jack
