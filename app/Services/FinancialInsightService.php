<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;

class FinancialInsightService
{
    /**
     * Generate financial insights for the given user, month, and year.
     */
    public function getInsights(User $user, int $month, int $year): array
    {
        $insights = [];

        $baseQuery = fn () => Transaction::whereHas('account', fn ($q) => $q->where('user_id', $user->id))
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year);

        $totalIncome = (float) $baseQuery()->where('type', 'income')->sum('amount');
        $totalExpense = (float) $baseQuery()->where('type', 'expense')->sum('amount');

        // 1. Savings Rate
        if ($totalIncome > 0) {
            $savingsRate = round((($totalIncome - $totalExpense) / $totalIncome) * 100, 1);
            $insights[] = [
                'type' => $savingsRate >= 20 ? 'positive' : ($savingsRate >= 0 ? 'warning' : 'negative'),
                'icon' => 'piggy-bank',
                'title' => 'Savings Rate',
                'description' => $savingsRate >= 20
                    ? "Great job! You're saving {$savingsRate}% of your income this month."
                    : ($savingsRate >= 0
                        ? "You're saving {$savingsRate}% of your income. Try to aim for at least 20%."
                        : "You're spending more than you earn. Your savings rate is {$savingsRate}%."),
                'value' => $savingsRate . '%',
            ];
        }

        // 2. Need/Want/Investment Ratio (50/30/20 rule)
        if ($totalExpense > 0) {
            $needTotal = (float) $baseQuery()->where('type', 'expense')->where('classification', 'need')->sum('amount');
            $wantTotal = (float) $baseQuery()->where('type', 'expense')->where('classification', 'want')->sum('amount');
            $investTotal = (float) $baseQuery()->where('type', 'expense')->where('classification', 'investment')->sum('amount');

            $needPct = round(($needTotal / $totalExpense) * 100);
            $wantPct = round(($wantTotal / $totalExpense) * 100);
            $investPct = round(($investTotal / $totalExpense) * 100);

            $isHealthy = $needPct <= 55 && $wantPct <= 35;
            $insights[] = [
                'type' => $isHealthy ? 'positive' : 'warning',
                'icon' => 'pie-chart',
                'title' => 'Spending Balance (50/30/20 Rule)',
                'description' => "Need: {$needPct}% · Want: {$wantPct}% · Investment: {$investPct}%. "
                    . ($isHealthy
                        ? 'Your spending balance looks healthy!'
                        : 'Consider reducing wants and increasing savings.'),
                'value' => "{$needPct}/{$wantPct}/{$investPct}",
            ];
        }

        // 3. Top Spending Category
        $topCategory = $baseQuery()
            ->where('type', 'expense')
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($txns) => [
                'name' => $txns->first()->category?->name ?? 'Uncategorized',
                'total' => (float) $txns->sum('amount'),
            ])
            ->sortByDesc('total')
            ->first();

        if ($topCategory) {
            $pct = $totalExpense > 0 ? round(($topCategory['total'] / $totalExpense) * 100) : 0;
            $insights[] = [
                'type' => 'info',
                'icon' => 'trending-up',
                'title' => 'Top Spending Category',
                'description' => "You spent the most on {$topCategory['name']} — accounting for {$pct}% of your total expenses.",
                'value' => $this->formatCurrency($topCategory['total']),
            ];
        }

        // 4. Biggest Single Expense
        $biggestExpense = $baseQuery()
            ->where('type', 'expense')
            ->with(['category', 'account'])
            ->orderByDesc('amount')
            ->first();

        if ($biggestExpense) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'alert-circle',
                'title' => 'Biggest Single Expense',
                'description' => sprintf(
                    '%s on %s (%s)',
                    $biggestExpense->description ?: $biggestExpense->category?->name ?? 'Uncategorized',
                    $biggestExpense->transaction_date->format('M d'),
                    $biggestExpense->account?->name ?? '-'
                ),
                'value' => $this->formatCurrency($biggestExpense->amount),
            ];
        }

        // 5. Budget Alerts — categories where spending exceeds budget
        $budgets = Budget::whereHas('category', fn ($q) => $q->where('user_id', $user->id))
            ->where('month', $month)
            ->where('year', $year)
            ->with('category')
            ->get();

        foreach ($budgets as $budget) {
            $spent = (float) $baseQuery()
                ->where('type', 'expense')
                ->where('category_id', $budget->category_id)
                ->sum('amount');

            if ($spent > (float) $budget->amount) {
                $overBy = $spent - (float) $budget->amount;
                $insights[] = [
                    'type' => 'negative',
                    'icon' => 'alert-triangle',
                    'title' => 'Budget Exceeded: ' . ($budget->category?->name ?? 'Unknown'),
                    'description' => sprintf(
                        'You exceeded your %s budget by %s (Budget: %s, Spent: %s).',
                        $budget->category?->name ?? 'Unknown',
                        $this->formatCurrency($overBy),
                        $this->formatCurrency($budget->amount),
                        $this->formatCurrency($spent)
                    ),
                    'value' => '+' . $this->formatCurrency($overBy),
                ];
            }
        }

        // 6. Spending Trend (vs previous month)
        $prevMonth = $month === 1 ? 12 : $month - 1;
        $prevYear = $month === 1 ? $year - 1 : $year;

        $prevExpense = (float) Transaction::whereHas('account', fn ($q) => $q->where('user_id', $user->id))
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $prevMonth)
            ->whereYear('transaction_date', $prevYear)
            ->sum('amount');

        if ($prevExpense > 0 && $totalExpense > 0) {
            $changePct = round((($totalExpense - $prevExpense) / $prevExpense) * 100, 1);
            $direction = $changePct > 0 ? 'increased' : 'decreased';
            $insights[] = [
                'type' => $changePct > 10 ? 'warning' : ($changePct < 0 ? 'positive' : 'info'),
                'icon' => $changePct > 0 ? 'trending-up' : 'trending-down',
                'title' => 'Spending Trend',
                'description' => sprintf(
                    'Your spending %s by %s%% compared to last month (%s → %s).',
                    $direction,
                    abs($changePct),
                    $this->formatCurrency($prevExpense),
                    $this->formatCurrency($totalExpense)
                ),
                'value' => ($changePct > 0 ? '+' : '') . $changePct . '%',
            ];
        }

        return $insights;
    }

    /**
     * Format a number as IDR currency.
     */
    private function formatCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
