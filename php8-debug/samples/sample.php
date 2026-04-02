<?php
$a = str_repeat('x', 10);
xdebug_debug_zval('a');

echo "\$b=\$a.....\n";
$b = $a;
xdebug_debug_zval('a');
xdebug_debug_zval('b');

echo "\$c=&\$a.....\n";
$c = &$a;
xdebug_debug_zval('a');
xdebug_debug_zval('b');
xdebug_debug_zval('c');

echo "\$a変更.....\n";
$a = str_repeat('z', 10);
xdebug_debug_zval('a');
xdebug_debug_zval('b');
xdebug_debug_zval('c');
