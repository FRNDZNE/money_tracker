<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MonthlyReportExport implements WithMultipleSheets
{
    public function __construct(
        private readonly array $report,
    ) {}

    public function sheets(): array
    {
        return [
            new MonthlyReportSummarySheet($this->report),
            new MonthlyReportCategorySheet('Expense Categories', $this->report['expense_categories']),
            new MonthlyReportCategorySheet('Income Categories', $this->report['income_categories']),
        ];
    }
}
