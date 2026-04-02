<?php

declare(strict_types=1);

namespace Samples\Tests;
echo getmypid() . " ModuleTest.php....\n";

use Mockery;
use PHPUnit\Framework\TestCase;
use Module\MainModule;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

echo getmypid() . " ModuleTest.php class....\n";

final class ModuleTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }
    
    public function testAdd(): void
    {
        echo getmypid() . " testAdd....\n";
        $main = new MainModule();
        $act = $main->addMain(1,2,3);
        self::assertSame(6, $act);
    }

    public function testAddMainStatic(): void
    {
        echo getmypid() . " testAddMainStatic....\n";
        $main = new MainModule();
        $act = $main->addMainStatic(1,2,3);
        self::assertSame(6, $act);
    }

    #[RunInSeparateProcess] // 別プロセスでテスト
    public function testMockAdd(): void
    {
        echo getmypid() . " testMockAdd....\n";
        // overloadは本物が呼ばれる前じゃないと実行できない。
        // そのため、本物のSubModuleを使うテストとは別のプロセスで動かす必要がある
        $mock = \Mockery::mock('overload:Module\SubModule');
        $mock->shouldReceive('add')->andReturn(10);
        $main = new MainModule();
        $act = $main->addMain(1,2,3);
        self::assertSame(13, $act);
    }

    #[RunInSeparateProcess] // 別プロセスでテスト
    public function testMockAddMainStatic(): void
    {
        echo getmypid() . " testMockAddMainStatic....\n";
        // staticメソッドのモックはaliasを使う
        $mock = \Mockery::mock('alias:Module\SubModule');
        $mock->shouldReceive('staticAdd')->andReturn(10);
        $main = new MainModule();
        $act = $main->addMainStatic(1,2,3);
        self::assertSame(13, $act);
    }

    #[RunInSeparateProcess] // 別プロセスでテスト
    public function testMockAddMainStaticWithParam(): void
    {
        echo getmypid() . " testMockAddMainStaticWithParam....\n";
        $mock = \Mockery::mock('alias:Module\SubModule');
        $mock->shouldReceive('staticAdd')
            ->once()     // 1回だけ呼ばれる
            ->with(1, 2) // 引数がSubModule->staticAdd(1,2)で呼ばれる
            ->andReturn(10);
        $main = new MainModule();
        $act = $main->addMainStatic(1,2,3);
        self::assertSame(13, $act);
    }

    public function testSpy(): void
    {
        echo getmypid() . " testSpy....\n";
        $spy = \Mockery::spy('Module\MainModule');
        $spy->addMain(1,2,3);
        $spy->addMain(3,2,1);
        $spy->subtractMain(2,3,4);
        $spy->subtractMain(2,3,4);

        // addMainは(1,2,3), (3,2,1)
        $spy->shouldHaveReceived('addMain')->with(1,2,3)->once();
        $spy->shouldHaveReceived('addMain')->with(3,2,1)->once();
        // subtractMainは(2,3,4) x 2
        $spy->shouldHaveReceived('subtractMain')->with(2,3,4)->times(2);
        // addMainStaticの呼び出しはない
        $spy->shouldNotHaveReceived('addMainStatic');

        // 警告抑制用
        self::assertTrue(true);
    }

    public function testMockNoPartial(): void
    {
        echo getmypid() . " testMockNoPartial....\n";
        $mock = \Mockery::mock('Module\MainModule');
        $mock->shouldReceive('addMain')->andReturn(13);
        // モックアップの内容
        $act = $mock->addMain(1,2,3);
        self::assertSame(13, $act);

        // エラーになる
        // Mockery\Exception\BadMethodCallException: Received Mockery_0_Module_MainModule::subtractMain(), but no expectations were specified
        // $act = $mock->subtractMain(6, 1, 2);
        // self::assertSame(3, $act); 
    }

    public function testMockPartial(): void
    {
        echo getmypid() . " testMockPartial....\n";
        $mock = \Mockery::mock('Module\MainModule')->makePartial();
        $mock->shouldReceive('addMain')->andReturn(13);
        // モックアップの内容
        $act = $mock->addMain(1,2,3);
        self::assertSame(13, $act);

        // 本物が動作する
        $act = $mock->subtractMain(6, 1, 2);
        self::assertSame(3, $act);
    }
}
