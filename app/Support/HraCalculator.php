<?php

namespace App\Support;

class HraCalculator
{
    /**
     * Classic HRA exemption estimate (old regime style).
     *
     * @return array{exempt: float, taxable: float, rules: array<string, float>}
     */
    public static function compute(
        float $basic,
        float $hraReceived,
        float $rentPaid,
        bool $metro = false
    ): array {
        $basic = max(0, $basic);
        $hraReceived = max(0, $hraReceived);
        $rentPaid = max(0, $rentPaid);

        $pctOfSalary = $metro ? 0.50 : 0.40;
        $rule1 = $hraReceived;
        $rule2 = $basic * $pctOfSalary;
        $rule3 = max(0, $rentPaid - ($basic * 0.10));

        $exempt = min($rule1, $rule2, $rule3);
        $taxable = max(0, $hraReceived - $exempt);

        return [
            'exempt' => round($exempt, 2),
            'taxable' => round($taxable, 2),
            'rules' => [
                'actual_hra' => round($rule1, 2),
                'salary_percent' => round($rule2, 2),
                'rent_minus_10' => round($rule3, 2),
            ],
        ];
    }
}
