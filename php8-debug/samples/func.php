<?php

function byValue($v): void
{
    echo "byValue:\n";
    xdebug_debug_zval('v');
}

function byRef(&$v): void
{
    echo "byRef:\n";
    xdebug_debug_zval('v');
}

xdebug_debug_zval('a');

$a = str_repeat('h', 1) . str_repeat('ello', 1);
$b = $a;
echo "before:\n";
var_dump($a);
var_dump($b);
debug_zval_dump($a);
debug_zval_dump($b);
xdebug_debug_zval('a');
xdebug_debug_zval('b');
$b = $a . "XX";
xdebug_debug_zval('a');
xdebug_debug_zval('b');

byRef($a);
byValue($a);

echo "after:\n";
xdebug_debug_zval('a');