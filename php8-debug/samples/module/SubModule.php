<?php
namespace Module;
echo " load. SubModule.php....\n";

class SubModule {
    private int $a;
    private int $b;
    public function __construct(int $a, int $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
    public function add(): int {
        return $this->a + $this->b;
    }
    public function subtract(): int {
        return $this->a - $this->b;
    }
    static function staticAdd(int $a, int $b): int {
        return $a + $b;
    }
}
