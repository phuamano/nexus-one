<?php

declare(strict_types=1);

namespace App\Services\Finanzas;

use App\Models\Finanzas\AccountPayable;
use App\Models\Finanzas\AccountReceivable;
use App\Models\Finanzas\FinancialMovement;

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

    public function financialAccountsSummary(): array
    {
        $accounts = \App\Models\Finanzas\FinancialAccount::query()
            ->where('is_active', true)
            ->get();

        return [
            'total_accounts' => $accounts->count(),

            'cash' => (float) $accounts
                ->where('type', \App\Enums\Finanzas\FinancialAccountType::CASH)
                ->sum(fn ($account) => (float) $account->current_balance),

            'bank' => (float) $accounts
                ->where('type', \App\Enums\Finanzas\FinancialAccountType::BANK)
                ->sum(fn ($account) => (float) $account->current_balance),

            'total_balance' => (float) $accounts
                ->sum(fn ($account) => (float) $account->current_balance),

            'accounts' => $accounts->map(fn ($account) => [
                'id' => $account->id,
                'name' => $account->name,
                'code' => $account->code,
                'type' => $account->type->value,
                'currency' => $account->currency,
                'balance' => (float) $account->current_balance,
            ])->values()->all(),
        ];
    }

    public function cashFlowSummary(): array
    {
        $movements = \App\Models\Finanzas\FinancialMovement::query()
            ->selectRaw('
            direction,
            COALESCE(SUM(amount), 0) as total
        ')
            ->groupBy('direction')
            ->get()
            ->keyBy('direction');

        $income = (float) ($movements['in']->total ?? 0);
        $expense = (float) ($movements['out']->total ?? 0);

        return [
            'income' => $income,
            'expense' => $expense,
            'net' => $income - $expense,
        ];
    }

    public function recentMovements(int $limit = 10): array
    {
        return \App\Models\Finanzas\FinancialMovement::query()
            ->with('account')
            ->latest('movement_date')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($movement) => [
                'id' => $movement->id,
                'date' => $movement->movement_date->toDateString(),
                'account' => $movement->account->name,
                'type' => $movement->type->value,
                'direction' => $movement->direction->value,
                'amount' => (float) $movement->amount,
                'reference' => $movement->reference,
                'notes' => $movement->notes,
            ])
            ->all();
    }

    public function cashFlowByMonth(int $months = 6): array
    {
        $startDate = now()
            ->startOfMonth()
            ->subMonths($months - 1);

        $movements = FinancialMovement::query()
            ->whereDate('movement_date', '>=', $startDate)
            ->selectRaw("
            DATE_TRUNC('month', movement_date) as month,
            direction,
            COALESCE(SUM(amount), 0) as total
        ")
            ->groupByRaw("DATE_TRUNC('month', movement_date), direction")
            ->orderByRaw("DATE_TRUNC('month', movement_date)")
            ->get();

        $result = [];

        for ($i = 0; $i < $months; $i++) {
            $date = $startDate->copy()->addMonths($i);

            $key = $date->format('Y-m');

            $result[$key] = [
                'label' => $date->translatedFormat('M Y'),
                'income' => 0.0,
                'expense' => 0.0,
            ];
        }

        foreach ($movements as $movement) {
            $key = \Carbon\Carbon::parse($movement->month)
                ->format('Y-m');

            if (! isset($result[$key])) {
                continue;
            }

            if ($movement->direction === 'in') {
                $result[$key]['income'] = (float) $movement->total;
            }

            if ($movement->direction === 'out') {
                $result[$key]['expense'] = (float) $movement->total;
            }
        }

        return array_values($result);
    }
}
