<?php
// 配列の代入はcopy-on-write
$a = [1,2,3];
// var_dump($a); // args->value->arr: 0x7f08ee65b300
xdebug_debug_zval('a');

echo "\$b = \$a .... 代入\n";
$b = $a;
// var_dump($b); // args->value->arr: 0x7f08ee65b300 *$aと同じアドレス
xdebug_debug_zval('a');  // [1,2,3]
xdebug_debug_zval('b');  // [1,2,3]

echo "\$b = &\$c\n";
$c = &$a;
xdebug_debug_zval('a');  // [1,2,3]
xdebug_debug_zval('b');  // [1,2,3]
xdebug_debug_zval('c');  // [1,2,3]

echo "\$b[0] = 99 .... コピーが発生する\n";
$b[0] = 99; // ここでコピー
// var_dump($b); // args->value->arr: 0x7f08ee65b360 アドレスが変わる（=コピー発生)
xdebug_debug_zval('a');  // [1,2,3]
xdebug_debug_zval('b');  // [99,2,3]
xdebug_debug_zval('c');  // [1,2,3]

echo "\$a = ['ab','cd']\n";
$a = ['ab','cd'];
xdebug_debug_zval('a');  // ['ab','cd']
xdebug_debug_zval('b');
xdebug_debug_zval('c');  // ['ab','cd']
