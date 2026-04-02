<?php

declare(strict_types=1);

namespace Samples\Tests;

use Mockery;
use PHPUnit\Framework\TestCase;

final class SampleTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_mockery_is_available(): void
    {
        $mock = Mockery::mock();
        $mock->shouldReceive('ping')->once()->andReturn('pong');

        self::assertSame('pong', $mock->ping());
    }
}
