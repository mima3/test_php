<?php
// nullableな書き方の例
declare(strict_types=1);

class C {}

// 旧来の書き方　null 既定値が暗黙に nullable を意味する
function f1(C $c = null) {
    var_dump($c);
}

f1(new C);
f1(null);

// union型を表現する
function f2(C | null $c) {
    var_dump($c);
}

f2(new C);
f2(null);

// Nullable 型にする
function f3(?C $c) {
    var_dump($c);
}

f3(new C);
f3(null);