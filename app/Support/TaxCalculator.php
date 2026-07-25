<?php

namespace App\Support;

class TaxCalculator
{
    /**
     * Simplified FY 2025-26 estimate for resident individuals (salary-style income).
     * Includes standard deduction, basic slabs, §87A rebate where applicable, and 4% cess.
     * Excludes surcharge, marginal relief, special rates (LTCG etc.), and other schedules.
     */
    public static function computeTaxSummary(float $gross, float $deductions, float $tds = 0): array
    {
        $gross = max(0, $gross);
        $deductions = max(0, min($deductions, $gross));
        $tds = max(0, $tds);

        $stdNew = 75000.0;
        $stdOld = 50000.0;

        $newTaxable = max(0, $gross - $stdNew);
        $oldTaxable = max(0, $gross - $stdOld - $deductions);

        $newTax = self::taxWithRebateAndCess($newTaxable, 'new');
        $oldTax = self::taxWithRebateAndCess($oldTaxable, 'old');

        $better = $newTax <= $oldTax ? 'new' : 'old';
        $saving = abs($oldTax - $newTax);

        return [
            'gross_salary' => $gross,
            'total_deductions' => $deductions,
            'standard_deduction_old' => $stdOld,
            'standard_deduction_new' => $stdNew,
            'taxable_old' => $oldTaxable,
            'taxable_new' => $newTaxable,
            'tax_old_regime' => $oldTax,
            'tax_new_regime' => $newTax,
            'tds_deducted' => $tds,
            'payable_or_refund_old' => round($oldTax - $tds, 2),
            'payable_or_refund_new' => round($newTax - $tds, 2),
            'recommended' => $better,
            'saving' => $saving,
        ];
    }

    private static function taxWithRebateAndCess(float $taxable, string $regime): float
    {
        $tax = $regime === 'new' ? self::slabTaxNew($taxable) : self::slabTaxOld($taxable);

        // §87A — rebate before health & education cess
        if ($regime === 'new' && $taxable <= 1200000) {
            $tax = max(0, $tax - min($tax, 60000));
        } elseif ($regime === 'old' && $taxable <= 500000) {
            $tax = max(0, $tax - min($tax, 12500));
        }

        return round($tax * 1.04, 2);
    }

    private static function slabTaxNew(float $taxable): float
    {
        $tax = 0.0;
        $slabs = [
            [400000, 0.00],
            [800000, 0.05],
            [1200000, 0.10],
            [1600000, 0.15],
            [2000000, 0.20],
            [2400000, 0.25],
            [PHP_FLOAT_MAX, 0.30],
        ];
        $prev = 0.0;
        foreach ($slabs as [$upto, $rate]) {
            if ($taxable <= $prev) {
                break;
            }
            $chunk = min($taxable, $upto) - $prev;
            $tax += $chunk * $rate;
            $prev = $upto;
        }

        return $tax;
    }

    private static function slabTaxOld(float $taxable): float
    {
        $tax = 0.0;
        $slabs = [
            [250000, 0.00],
            [500000, 0.05],
            [1000000, 0.20],
            [PHP_FLOAT_MAX, 0.30],
        ];
        $prev = 0.0;
        foreach ($slabs as [$upto, $rate]) {
            if ($taxable <= $prev) {
                break;
            }
            $chunk = min($taxable, $upto) - $prev;
            $tax += $chunk * $rate;
            $prev = $upto;
        }

        return $tax;
    }
}
