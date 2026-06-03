<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;

class ReportService
{
    /**
     * Get a monthly summary for every month in the given year.
     * Returns an array of 12 items with month, income, expense, and net.
     */
    public function getMonthlySummaries(User $user, int $year): array
    {
        $summaries = [];

        for ($month = 1; $month <= 12; $month++) {
            $income = (float) Transaction::whereHas('account', fn ($q) => $q->where('user_id', $user->id))
                ->where('type', 'income')
                ->whereMonth('transaction_date', $month)
                ->whereYear('transaction_date', $year)
                ->sum('amount');

            $expense = (float) Transaction::whereHas('account', fn ($q) => $q->where('user_id', $user->id))
                ->where('type', 'expense')
                ->whereMonth('transaction_date', $month)
                ->whereYear('transaction_date', $year)
                ->sum('amount');

            $summaries[] = [
                'month'   => $month,
                'label'   => date('M', mktime(0, 0, 0, $month, 1)),
                'income'  => $income,
                'expense' => $expense,
                'net'     => $income - $expense,
            ];
        }

        return $summaries;
    }

    /**
     * Get a detailed report for a specific month/year.
     * Includes income, expense, net, and per-category breakdown.
     */
    public function getMonthlyReport(User $user, int $month, int $year): array
    {
        $baseQuery = fn () => Transaction::whereHas('account', fn ($q) => $q->where('user_id', $user->id))
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year);

        $totalIncome  = (float) $baseQuery()->where('type', 'income')->sum('amount');
        $totalExpense = (float) $baseQuery()->where('type', 'expense')->sum('amount');

        // Per-category breakdown for expenses
        $categoryBreakdown = $baseQuery()
            ->where('type', 'expense')
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($transactions) {
                $category = $transactions->first()->category;

                return [
                    'category_id'   => $category?->id,
                    'category_name' => $category?->name ?? 'Uncategorized',
                    'total'         => (float) $transactions->sum('amount'),
                    'count'         => $transactions->count(),
                ];
            })
            ->values();

        // Per-category breakdown for income
        $incomeBreakdown = $baseQuery()
            ->where('type', 'income')
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($transactions) {
                $category = $transactions->first()->category;

                return [
                    'category_id'   => $category?->id,
                    'category_name' => $category?->name ?? 'Uncategorized',
                    'total'         => (float) $transactions->sum('amount'),
                    'count'         => $transactions->count(),
                ];
            })
            ->values();

        return [
            'month'              => $month,
            'year'               => $year,
            'total_income'       => $totalIncome,
            'total_expense'      => $totalExpense,
            'net'                => $totalIncome - $totalExpense,
            'expense_categories' => $categoryBreakdown,
            'income_categories'  => $incomeBreakdown,
        ];
    }

    /**
     * Get the available years that have transaction data for the user.
     */
    public function getAvailableYears(User $user): array
    {
        $years = Transaction::whereHas('account', fn ($q) => $q->where('user_id', $user->id))
            ->selectRaw('EXTRACT(YEAR FROM transaction_date)::integer AS year')
            ->distinct()
            ->orderByRaw('year DESC')
            ->pluck('year')
            ->toArray();

        // Always include the current year
        $currentYear = (int) now()->format('Y');
        if (! in_array($currentYear, $years)) {
            array_unshift($years, $currentYear);
        }

        return $years;
    }
}
