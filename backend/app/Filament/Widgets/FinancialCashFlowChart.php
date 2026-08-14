<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\Finanzas\FinancialReportService;
use Filament\Widgets\ChartWidget;

class FinancialCashFlowChart extends ChartWidget
{
    protected ?string $heading = 'Flujo de caja';

    protected function getData(): array
    {
        $months = app(FinancialReportService::class)
            ->cashFlowByMonth(6);

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => array_column($months, 'income'),
                ],
                [
                    'label' => 'Gastos',
                    'data' => array_column($months, 'expense'),
                ],
            ],
            'labels' => array_column($months, 'label'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
