<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class MonthlyReportSummarySheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(
        private readonly array $report,
    ) {}

    public function title(): string
    {
        return 'Summary';
    }

    public function headings(): array
    {
        return ['Metric', 'Amount'];
    }

    public function collection(): Collection
    {
        return collect([
            ['Total Income', $this->report['total_income']],
            ['Total Expense', $this->report['total_expense']],
            ['Net', $this->report['net']],
        ]);
    }
}
