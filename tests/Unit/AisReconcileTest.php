<?php

namespace Tests\Unit;

use App\Support\AisReconcile;
use PHPUnit\Framework\TestCase;

class AisReconcileTest extends TestCase
{
    public function test_match_within_one_rupee(): void
    {
        $result = AisReconcile::compare(10000.4, 10000.9);

        $this->assertTrue($result['match']);
    }

    public function test_mismatch_message(): void
    {
        $result = AisReconcile::compare(12000, 10000);

        $this->assertFalse($result['match']);
        $this->assertSame(2000.0, $result['difference']);
        $this->assertStringContainsString('Mismatch', $result['message']);
    }
}
