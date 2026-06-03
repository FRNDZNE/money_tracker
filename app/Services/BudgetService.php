<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class BudgetService
{
    /**
     * Get all budgets for the given user in the specified month/year,
     * with the actual spent amount computed for each.
     */
    public function getForUserAndMonth(User $user, int $month, int $year): Collection
    {
        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->with('category')
            ->get();

        return $budgets->map(function (Budget $budget) use ($month, $year) {
            $spent = (float) Transaction::where('account_id', function ($q) use ($budget) {
                $q->select('id')
                    ->from('accounts')
                    ->where('user_id', $budget->user_id);
            })
                ->where('category_id', $budget->category_id)
                ->where('type', 'expense')
                ->whereMonth('transaction_date', $month)
                ->whereYear('transaction_date', $year)
                ->sum('amount');

            $budget->spent = $spent;

            return $budget;
        });
    }

    /**
     * Get all expense categories for the user that can have a budget set.
     */
    public function getEligibleCategories(User $user, int $month, int $year): Collection
    {
        $budgetedCategoryIds = Budget::where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->pluck('category_id');

        return $user->categories()
            ->where('type', 'expense')
            ->whereNotIn('id', $budgetedCategoryIds)
            ->orderBy('name')
            ->get();
    }

    /**
     * Create or update a budget for the given user, category, month, and year.
     */
    public function upsert(User $user, int $categoryId, int $month, int $year, float $amount): Budget
    {
        return Budget::updateOrCreate(
            [
                'user_id'     => $user->id,
                'category_id' => $categoryId,
                'month'       => $month,
                'year'        => $year,
            ],
            ['amount' => $amount]
        );
    }

    /**
     * Delete a budget.
     */
    public function delete(Budget $budget): void
    {
        $budget->delete();
    }
}
