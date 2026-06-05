<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly Collection $transactions,
    ) {}

    public function collection(): Collection
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Type',
            'Category',
            'Sub Category',
            'Account',
            'Amount',
            'Classification',
            'Description',
        ];
    }

    /**
     * @param  \App\Models\Transaction  $transaction
     */
    public function map($transaction): array
    {
        return [
            $transaction->transaction_date->format('Y-m-d'),
            ucfirst($transaction->type),
            $transaction->category?->name ?? '-',
            $transaction->subCategory?->name ?? '-',
            $transaction->account?->name ?? '-',
            $transaction->amount,
            $transaction->classification ? ucfirst($transaction->classification) : '-',
            $transaction->description ?? '-',
        ];
    }
}
