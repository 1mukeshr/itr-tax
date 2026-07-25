<?php

namespace Tests\Unit;

use App\Support\HraCalculator;
use PHPUnit\Framework\TestCase;

class HraCalculatorTest extends TestCase
{
    public function test_exemption_is_least_of_three_rules(): void
    {
        $result = HraCalculator::compute(600000, 240000, 300000, false);

        $this->assertSame(240000.0, $result['rules']['actual_hra']);
        $this->assertSame(240000.0, $result['rules']['salary_percent']); // 40% of 6L
        $this->assertSame(240000.0, $result['rules']['rent_minus_10']); // 3L - 60k
        $this->assertSame(240000.0, $result['exempt']);
        $this->assertSame(0.0, $result['taxable']);
    }

    public function test_metro_uses_fifty_percent(): void
    {
        $result = HraCalculator::compute(600000, 400000, 360000, true);

        $this->assertSame(300000.0, $result['rules']['salary_percent']);
        $this->assertSame(300000.0, $result['exempt']);
        $this->assertSame(100000.0, $result['taxable']);
    }
}
