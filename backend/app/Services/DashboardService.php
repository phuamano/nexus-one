<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Compras\Purchase;
use App\Models\Ventas\Sale;
use App\Services\Finanzas\FinancialReportService;

class DashboardService
{
    public function __construct(
        private readonly FinancialReportService $financialReportService,
        private readonly InventoryReportService $inventoryReportService,
    ) {
    }

    public function summary(): array
    {
        return [
            'financial' => [
                'accounts' => $this->financialReportService
                    ->financialAccountsSummary(),

                'cash_flow' => $this->financialReportService
                    ->cashFlowSummary(),

                'receivables' => $this->financialReportService
                    ->receivablesSummary(),

                'payables' => $this->financialReportService
                    ->payablesSummary(),
            ],

            'inventory' => [
                'low_stock' => $this->inventoryReportService
                    ->lowStock(),
            ],

            'sales' => $this->salesSummary(),

            'purchases' => $this->purchasesSummary(),
        ];
    }

    private function salesSummary(): array
    {
        $summary = Sale::query()
            ->selectRaw('
            COUNT(*) as count,
            COALESCE(SUM(subtotal), 0) as subtotal,
            COALESCE(SUM(tax), 0) as tax,
            COALESCE(SUM(total), 0) as total
        ')
            ->first();

        return [
            'count' => (int) $summary->count,
            'subtotal' => (float) $summary->subtotal,
            'tax' => (float) $summary->tax,
            'total' => (float) $summary->total,
        ];
    }

    private function purchasesSummary(): array
    {
        $summary = Purchase::query()
            ->selectRaw('
            COUNT(*) as count,
            COALESCE(SUM(subtotal), 0) as subtotal,
            COALESCE(SUM(tax), 0) as tax,
            COALESCE(SUM(total), 0) as total
        ')
            ->first();

        return [
            'count' => (int) $summary->count,
            'subtotal' => (float) $summary->subtotal,
            'tax' => (float) $summary->tax,
            'total' => (float) $summary->total,
        ];
    }
}
