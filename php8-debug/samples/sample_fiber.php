<?php

$f1 = new Fiber(function (): void {
    echo "  A1\n";
    $value = Fiber::suspend();
    echo "  A2" . $value . "\n";
    $value = Fiber::suspend();
    echo "  A3" . $value . "\n";
});

$f2 = new Fiber(function (): void {
    echo "  B1\n";
    $value = Fiber::suspend();
    echo "  B2" . $value . "\n";
    $value = Fiber::suspend();
    echo "  B3" . $value . "\n";
});

echo "main...start\n";
$f1->start();   // A1
$f2->start();   // B1
echo "main...started\n";
$f1->resume("f1 resume 1");  // A2
echo "main...1\n";
$f2->resume("f2 resume 1");  // B2
echo "main...2\n";
$f1->resume("f1 resume 2");  // A3
echo "main...3\n";
$f2->resume("f2 resume 2");  // B3
echo "main...end\n";
