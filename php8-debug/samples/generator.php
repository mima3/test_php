<?php
function sub() {
    yield 1;
    yield 2;
}

function main() {
    yield from sub();
    yield 3;
}

foreach (main() as $v) {
    echo $v, PHP_EOL;
}

echo "------------\n";

function printer(): Generator {
    while (true) {
        $msg = yield;
        echo $msg, PHP_EOL;
    }
}

$g = printer();
$g->send('Hello');
$g->send('World');
