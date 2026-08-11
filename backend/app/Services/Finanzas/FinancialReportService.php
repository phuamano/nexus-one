<?php

declare(strict_types=1);

namespace App\Services\Finanzas;

use App\Models\Finanzas\AccountPayable;
use App\Models\Finanzas\AccountReceivable;

class FinancialReportService
{
    public function receivablesSummary(): array
    {
        $summary = AccountReceivable::query()
            ->selectRaw('
            COALESCE(SUM(amount), 0) as total,
            COALESCE(SUM(paid_amount), 0) as paid,
            COALESCE(SUM(balance), 0) as balance
        ')
            ->first();

        $statuses = AccountReceivable::query()
            ->selectRaw('
            status,
            COUNT(*) as count,
            COALESCE(SUM(balance), 0) as balance
        ')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $overdue = AccountReceivable::query()
            ->where('balance', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->selectRaw('
        COUNT(*) as count,
        COALESCE(SUM(balance), 0) as balance
    ')
            ->first();

        return [
            'total' => (float) $summary->total,
            'paid' => (float) $summary->paid,
            'balance' => (float) $summary->balance,

            'pending' => (float) (
                $statuses['pending']->balance ?? 0
            ),

            'partial' => (float) (
                $statuses['partial']->balance ?? 0
            ),

            'paid_accounts' => (int) (
                $statuses['paid']->count ?? 0
            ),

            'total_accounts' => (int) (
            $statuses->sum('count')
            ),

            'overdue' => [
                'count' => (int) $overdue->count,
                'balance' => (float) $overdue->balance,
            ],
        ];
    }

    public function payablesSummary(): array
    {
        $summary = AccountPayable::query()
            ->selectRaw('
            COALESCE(SUM(amount), 0) as total,
            COALESCE(SUM(paid_amount), 0) as paid,
            COALESCE(SUM(balance), 0) as balance
        ')
            ->first();

        $statuses = AccountPayable::query()
            ->selectRaw('
            status,
            COUNT(*) as count,
            COALESCE(SUM(balance), 0) as balance
        ')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $overdue = AccountPayable::query()
            ->where('balance', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->selectRaw('
        COUNT(*) as count,
        COALESCE(SUM(balance), 0) as balance
    ')
            ->first();

        return [
            'total' => (float) $summary->total,
            'paid' => (float) $summary->paid,
            'balance' => (float) $summary->balance,

            'pending' => (float) (
                $statuses['pending']->balance ?? 0
            ),

            'partial' => (float) (
                $statuses['partial']->balance ?? 0
            ),

            'paid_accounts' => (int) (
                $statuses['paid']->count ?? 0
            ),

            'total_accounts' => (int) (
            $statuses->sum('count')
            ),

            'overdue' => [
                'count' => (int) $overdue->count,
                'balance' => (float) $overdue->balance,
            ],
        ];
    }
}
