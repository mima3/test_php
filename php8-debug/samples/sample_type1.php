<?php
// https://www.php.net/manual/ja/language.types.declarations.php
declare(strict_types=0); // 省略と同じ挙動

function sum(int $a, int $b): int {
    echo "$a + $b \n"; // この時点でintにキャストされる
    return $a + $b;
}

var_dump(sum(1, 2));
/**
 * 以下の警告は表示されるが処理は続行する
 * Deprecated: Implicit conversion from float 1.5 to int loses precision in /samples/sample_type1.php on line 4
 * Deprecated: Implicit conversion from float 2.5 to int loses precision in /samples/sample_type1.php on line 4
 */
var_dump(sum(1.5, 2.5));
