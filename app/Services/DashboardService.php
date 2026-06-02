<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function __construct(
        private readonly AccountService $accountService,
        private readonly TransactionService $transactionService,
    ) {}

    /**
     * Get summary metrics for the current month.
     */
    public function getSummary(User $user): array
    {
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;

        $totalIncome = $this->transactionService->getMonthlyTotal($user, 'income', $month, $year);
        $totalExpense = $this->transactionService->getMonthlyTotal($user, 'expense', $month, $year);

        return [
            'total_assets' => $this->accountService->getTotalAssets($user),
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_cash_flow' => $totalIncome - $totalExpense,
        ];
    }

    /**
     * Get balance per account for active accounts.
     */
    public function getAccountDistribution(User $user): Collection
    {
        return $user->accounts()
            ->where('is_active', true)
            ->get()
            ->map(function (Account $account) {
                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'balance' => $this->accountService->getBalance($account),
                ];
            });
    }

    /**
     * Get total expenses grouped by category for a given month/year.
     */
    public function getExpenseByCategory(User $user, int $month, int $year): Collection
    {
        return Transaction::whereHas('account', fn ($q) => $q->where('user_id', $user->id))
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($transactions) {
                $category = $transactions->first()->category;

                return [
                    'category' => $category?->name ?? 'Uncategorized',
                    'total' => (float) $transactions->sum('amount'),
                ];
            })
            ->values();
    }

    /**
     * Get spending totals by classification (need/want/investment) for a given month/year.
     */
    public function getSpendingClassification(User $user, int $month, int $year): array
    {
        $data = Transaction::whereHas('account', fn ($q) => $q->where('user_id', $user->id))
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->selectRaw('classification, SUM(amount) as total')
            ->groupBy('classification')
            ->pluck('total', 'classification');

        return [
            'need' => (float) ($data['need'] ?? 0),
            'want' => (float) ($data['want'] ?? 0),
            'investment' => (float) ($data['investment'] ?? 0),
        ];
    }

    /**
     * Get monthly income and expense trend for the last N months.
     */
    public function getCashFlowTrend(User $user, int $months = 6): array
    {
        $trend = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->month;
            $year = $date->year;

            $trend[] = [
                'month' => $date->format('M Y'),
                'income' => $this->transactionService->getMonthlyTotal($user, 'income', $month, $year),
                'expense' => $this->transactionService->getMonthlyTotal($user, 'expense', $month, $year),
            ];
        }

        return $trend;
    }

    /**
     * Get the most recent transactions.
     */
    public function getRecentTransactions(User $user, int $limit = 10): Collection
    {
        return $this->transactionService->getRecent($user, $limit);
    }
}
