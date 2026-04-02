<?php
// nullableな書き方の例
declare(strict_types=1);

// phpstanだと下記のエラー
// Function f1() has parameter $a with no value type specified in iterable type array.  
//    🪪  missingType.iterableValue 
// phpdocで@param list<int> $aなどを書けば対応可能
function f1(array $a): void {
    echo 'length: ' . count($a) . PHP_EOL;
}

f1([1, "a", 3]);
f1([
    "key1" => "contents1",
    "key2" => 22222,
    "key3" => "contents3",
]);


/**
 * @param list<int> $a
 */
function f2(array $a): void {
    echo 'length: ' . count($a) . PHP_EOL;
}
f2([1, 2, 3]);
// ./vendor/bin/phpstan analyse sample_type4.php --level=max
// Parameter #1 $a of function f2 expects list<int>, array{1, '2', 3} given.               
//     🪪  argument.type 
// psalm
// ERROR: InvalidArgument - sample_type4.php:24:4 - Argument 1 of f2 expects list<int>, but list{1, '2', 3} provided (see https://psalm.dev/004)
// 
f2([1, "2", 3]);

/**
 * @param array<array-key, string> $a
 */
function f3(array $a): void {
    echo 'length: ' . count($a) . PHP_EOL;
}

f3([
    "key1" => "contents1",
    "key2" => "22222",
    "key3" => "contents3",
]);
// ./vendor/bin/phpstan analyse sample_type4.php --level=max
// Parameter #1 $a of function f3 expects array<string>, array<string, int|string> given.  
//     🪪  argument.type 
// psalm
// ERROR: InvalidArgument - sample_type4.php:45:4 - Argument 1 of f3 expects array<array-key, string>, but array{key1: 'contents1', key2: 22222, key3: 'contents3'} provided (see https://psalm.dev/004)
f3([
    "key1" => "contents1",
    "key2" => 22222,
    "key3" => "contents3",
]);

/**
 * @param array{name: string, age: int} $a
 */
function f4(array $a): void {
    echo 'length: ' . count($a) . PHP_EOL;
}
f4([
    "name"=>"Trump",
    "age" => 79
]);

// phpstan
// 検出せず
// psalm
// ERROR: InvalidArgument - sample_type4.php:69:4 - Argument 1 of f4 expects array{age: int, name: string}, but array{Job: '大統領', age: 79, name: 'Trump'} with additional array shape fields (Job) was provided (see https://psalm.dev/004)
f4([
    "name"=>"Trump",
    "age" => 79,
    "Job" => "大統領"
]);

// phpstan
// Parameter #1 $a of function f4 expects array{name: string, age: int}, array{name:'Trump', age: '79'} given.
// 🪪  argument.type                                                                       
// Offset 'age' (int) does not accept type string. 
// psalm
// ERROR: InvalidArgument - sample_type4.php:86:4 - Argument 1 of f4 expects array{age: int, name: string}, but array{age: '79', name: 'Trump'} provided (see https://psalm.dev/004)
f4([
    "name"=>"Trump",
    "age" => "79"
]);