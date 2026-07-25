<?php

namespace App\Support;

class AisReconcile
{
    /**
     * Compare Form 16 TDS vs AIS/26AS TDS.
     *
     * @return array{match: bool, difference: float, message: string}
     */
    public static function compare(?float $form16Tds, ?float $aisTds): array
    {
        $a = round((float) $form16Tds, 2);
        $b = round((float) $aisTds, 2);
        $diff = round($a - $b, 2);

        if ($a <= 0 && $b <= 0) {
            return [
                'match' => true,
                'difference' => 0.0,
                'message' => 'Enter Form 16 TDS and AIS/26AS TDS to check for mismatches.',
            ];
        }

        if (abs($diff) < 1) {
            return [
                'match' => true,
                'difference' => $diff,
                'message' => 'Form 16 TDS and AIS/26AS TDS look aligned (within ₹1).',
            ];
        }

        return [
            'match' => false,
            'difference' => $diff,
            'message' => 'Mismatch of ₹'.number_format(abs($diff), 2).'. Reconcile before filing to avoid processing delays.',
        ];
    }
}
