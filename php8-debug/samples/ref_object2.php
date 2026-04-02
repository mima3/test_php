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
$c = clone $a;

/**
 * $a: id:1 name:Alice
 * $b: id:1 name:Alice
 * $c: id:2 name:Alice
 */
echo '$a: id:' . spl_object_id($a) . " name:" . $a->name . PHP_EOL;
echo '$b: id:' . spl_object_id($b) . " name:" . $b->name . PHP_EOL;
echo '$c: id:' . spl_object_id($c) . " name:" . $c->name . PHP_EOL;

$b->name = 'Bob';
/**
 * $a: id:1 name:Bob
 * $b: id:1 name:Bob
 * $c: id:2 name:Alice
 */
echo '$a: id:' . spl_object_id($a) . " name:" . $a->name . PHP_EOL;
echo '$b: id:' . spl_object_id($b) . " name:" . $b->name . PHP_EOL;
echo '$c: id:' . spl_object_id($c) . " name:" . $c->name . PHP_EOL;
