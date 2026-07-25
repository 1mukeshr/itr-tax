<?php

namespace App\Support;

use App\Models\ItrFiling;
use App\Models\TaxExpertAssignment;
use App\Models\User;

class ExpertAssigner
{
    /**
     * Pick the least-loaded available active tax expert.
     */
    public static function pickAvailable(): ?User
    {
        return User::query()
            ->withRole('ca')
            ->where('status', 'active')
            ->whereHas('profile', fn ($q) => $q->where('is_available', true))
            ->withCount([
                'assignedFilings as open_count' => fn ($q) => $q->whereNotIn('status', ['completed', 'cancelled', 'filed']),
            ])
            ->orderBy('open_count')
            ->orderBy('id')
            ->first();
    }

    public static function assign(ItrFiling $filing, User $expert, ?int $assignedBy = null, string $remark = 'Auto-assigned'): void
    {
        $oldStatus = $filing->status;

        $filing->update([
            'ca_id' => $expert->id,
            'status' => 'assigned',
        ]);

        TaxExpertAssignment::create([
            'order_id' => $filing->id,
            'tax_expert_id' => $expert->id,
            'assigned_by' => $assignedBy,
            'status' => 'active',
            'remark' => $remark,
            'assigned_at' => now(),
        ]);

        logFilingStatus($filing->id, $oldStatus, 'assigned', $assignedBy, $remark);
        ChatService::openForFiling(
            $filing->fresh(),
            'Hello! I am '.$expert->name.', your assigned tax expert for filing #'.$filing->id.'. You can message me here anytime.'
        );
    }
}
