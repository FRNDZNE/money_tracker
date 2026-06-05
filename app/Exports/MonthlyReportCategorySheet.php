<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class MonthlyReportCategorySheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(
        private readonly string $sheetTitle,
        private readonly Collection $categories,
    ) {}

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function headings(): array
    {
        return ['Category', 'Total', 'Transaction Count'];
    }

    public function collection(): Collection
    {
        return $this->categories->map(fn ($cat) => [
            $cat['category_name'],
            $cat['total'],
            $cat['count'],
        ]);
    }
}
