<?php

namespace App\Support;

/**
 * Document checklist helpers after Form 16 / AIS uploads.
 * Income figures must be entered by the user or tax expert (no invented values).
 */
class Form16Helper
{
    public static function mismatchHints(array $uploadedTypes): array
    {
        $hints = [];
        $hasForm16 = in_array('form16', $uploadedTypes, true);
        $hasAis = in_array('form26as', $uploadedTypes, true);

        if ($hasForm16 && ! $hasAis) {
            $hints[] = [
                'level' => 'warn',
                'title' => 'AIS / 26AS not uploaded',
                'body' => 'Reconcile Form 16 TDS with AIS / Form 26AS before filing to avoid refund delays.',
            ];
        }
        if ($hasForm16 && $hasAis) {
            $hints[] = [
                'level' => 'ok',
                'title' => 'Documents look complete',
                'body' => 'Form 16 and AIS/26AS are present. Enter income figures carefully on the tax summary.',
            ];
        }
        if (! $hasForm16) {
            $hints[] = [
                'level' => 'info',
                'title' => 'Upload Form 16',
                'body' => 'Form 16 is required for salaried filing. Upload it, then enter Part B figures on the summary screen.',
            ];
        }

        return $hints;
    }
}
