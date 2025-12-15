<?php

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

class DatabaseIsTest extends TestCase
{
    public function testDatabaseIsTest(): void
    {
        $this->assertArrayHasKey('DATABASE_URL', $_ENV);

        $this->assertStringContainsString(
            '_test',
            $_ENV['DATABASE_URL'],
            'La base utilisée par PHPUnit n’est PAS une base de test'
        );
    }
}
