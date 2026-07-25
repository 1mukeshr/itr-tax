<?php

namespace App\Support;

class Validation
{
    public static function pan(): array
    {
        return ['required', 'string', 'size:10', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/i'];
    }

    public static function panOptional(): array
    {
        return ['nullable', 'string', 'size:10', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/i'];
    }

    public static function phone(): array
    {
        return ['required', 'string', 'regex:/^[6-9]\d{9}$/'];
    }

    public static function phoneOptional(): array
    {
        return ['nullable', 'string', 'regex:/^[6-9]\d{9}$/'];
    }

    public static function pincodeOptional(): array
    {
        return ['nullable', 'string', 'regex:/^\d{6}$/'];
    }

    public static function docTypes(): array
    {
        return [
            'form16', 'form26as', 'pan_card', 'aadhaar',
            'bank_statement', 'investment_proof', 'capital_gains', 'other',
        ];
    }

    public static function moneyRequired(): array
    {
        return ['required', 'numeric', 'min:0', 'max:999999999'];
    }

    public static function moneyOptional(): array
    {
        return ['nullable', 'numeric', 'min:0', 'max:999999999'];
    }

    public static function ackNo(): array
    {
        return ['required', 'string', 'min:6', 'max:40', 'regex:/^[A-Za-z0-9\-]+$/'];
    }
}
