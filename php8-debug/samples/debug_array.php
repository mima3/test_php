<?php
// 配列の代入はcopy-on-write
$a = [1,2,3];
var_dump($a); // args->value->arr: 0x7f08ee65b300

echo "\$b = \$a .... 代入\n";
$b = $a;
var_dump($b); // args->value->arr: 0x7f08ee65b300 *$aと同じアドレス

echo "\$b[0] = 99 .... コピーが発生する\n";
$b[0] = 99; // ここでコピー
var_dump($b); // args->value->arr: 0x7f08ee65b360 アドレスが変わる（=コピー発生)
