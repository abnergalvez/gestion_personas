<?php

namespace Tests\Unit;

use App\Support\Rut;
use PHPUnit\Framework\TestCase;

class RutTest extends TestCase
{
    public function test_it_validates_a_known_rut(): void
    {
        $this->assertTrue(Rut::isValid('12.345.678-5'));
        $this->assertSame('12345678-5', Rut::normalize('12.345.678-5'));
        $this->assertSame('12.345.678-5', Rut::format('12345678-5'));
    }

    public function test_it_rejects_a_wrong_verifier(): void
    {
        $this->assertFalse(Rut::isValid('12.345.678-9'));
    }

    public function test_verifier_matches_the_modulus_algorithm(): void
    {
        $this->assertSame('5', Rut::verifier(12345678));
    }
}
