<?php
declare(strict_types=1);

function sum(int $a, int $b): int {
    echo "$a + $b \n"; // この時点でintにキャストされる
    return $a + $b;
}

var_dump(sum(1, 2));

/**
 * 以下のエラーが発生して止まる
 * Fatal error: Uncaught TypeError: sum(): Argument #1 ($a) must be of type int, float given, called in /samples/sample_type2.php on line 10 and defined in /samples/sample_type2.php on line 4
 * TypeError: sum(): Argument #1 ($a) must be of type int, float given, called in /samples/sample_type2.php on line 10 in /samples/sample_type2.php on line 4
 */
var_dump(sum(1.5, 2.5));
