<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\Finanzas\FinancialReportService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $report = app(FinancialReportService::class);

        $accounts = $report->financialAccountsSummary();
        $cashFlow = $report->cashFlowSummary();
        $receivables = $report->receivablesSummary();
        $payables = $report->payablesSummary();

        return [
            Stat::make(
                'Saldo disponible',
                'S/ ' . number_format($accounts['total_balance'], 2)
            )
                ->description(
                    $accounts['total_accounts'] . ' cuentas activas'
                )
                ->descriptionIcon('heroicon-m-building-library')
                ->color('success'),

            Stat::make(
                'Ingresos',
                'S/ ' . number_format($cashFlow['income'], 2)
            )
                ->description('Ingresos registrados')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make(
                'Gastos',
                'S/ ' . number_format($cashFlow['expense'], 2)
            )
                ->description('Gastos registrados')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make(
                'Resultado neto',
                'S/ ' . number_format($cashFlow['net'], 2)
            )
                ->description('Ingresos menos gastos')
                ->descriptionIcon('heroicon-m-calculator')
                ->color(
                    $cashFlow['net'] >= 0
                        ? 'success'
                        : 'danger'
                ),

            Stat::make(
                'Por cobrar',
                'S/ ' . number_format($receivables['balance'], 2)
            )
                ->description(
                    $receivables['overdue']['count'] . ' vencidas'
                )
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('warning'),

            Stat::make(
                'Por pagar',
                'S/ ' . number_format($payables['balance'], 2)
            )
                ->description(
                    $payables['overdue']['count'] . ' vencidas'
                )
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('danger'),
        ];
    }
}
