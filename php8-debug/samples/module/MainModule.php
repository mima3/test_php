<?php
namespace Module;
echo getmypid() . " load. MainModule.php....\n";
use Module\SubModule;

class MainModule {
    public function addMain(int $a, int $b, int $c): int {
        $sub = new SubModule($a, $b);
        return $sub->add() + $c;
    }
    public function subtractMain(int $a, int $b, int $c): int {
        $sub = new SubModule($a, $b);
        return $sub->subtract() - $c;
    }
    public function addMainStatic(int $a, int $b, int $c): int {
        $sub = new SubModule($a, $b);
        return SubModule::staticAdd($a, $b) + $c;
    }
}
