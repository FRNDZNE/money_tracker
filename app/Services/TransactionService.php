<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TransactionService
{
    /**
     * Get paginated transactions for the given user, with optional filters.
     */
    public function getPaginated(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Transaction::whereHas('account', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['account', 'category', 'subCategory'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['month'])) {
            $query->whereMonth('transaction_date', $filters['month']);
        }

        if (! empty($filters['year'])) {
            $query->whereYear('transaction_date', $filters['year']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', $filters['date_to']);
        }

        return $query->paginate(15)->withQueryString();
    }

    /**
     * Create a new transaction.
     */
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    /**
     * Update an existing transaction.
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);

        return $transaction->fresh();
    }

    /**
     * Delete a transaction.
     */
    public function delete(Transaction $transaction): void
    {
        $transaction->delete();
    }

    /**
     * Get the total amount for a given transaction type, month, and year.
     */
    public function getMonthlyTotal(User $user, string $type, int $month, int $year): float
    {
        return (float) Transaction::whereHas('account', fn ($q) => $q->where('user_id', $user->id))
            ->where('type', $type)
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->sum('amount');
    }

    /**
     * Get all filtered transactions (no pagination) for export.
     */
    public function getFiltered(User $user, array $filters = []): Collection
    {
        $query = Transaction::whereHas('account', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['account', 'category', 'subCategory'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['month'])) {
            $query->whereMonth('transaction_date', $filters['month']);
        }

        if (! empty($filters['year'])) {
            $query->whereYear('transaction_date', $filters['year']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', $filters['date_to']);
        }

        return $query->get();
    }

    /**
     * Get the most recent transactions for the given user.
     */
    public function getRecent(User $user, int $limit = 10): Collection
    {
        return Transaction::whereHas('account', fn ($q) => $q->where('user_id', $user->id))
            ->with(['account', 'category', 'subCategory'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get aggregated income/expense summary for the current filters.
     * Returns total_income, total_expense, net, and transaction count.
     */
    public function getSummary(User $user, array $filters = []): array
    {
        $base = Transaction::whereHas('account', fn ($q) => $q->where('user_id', $user->id));

        if (! empty($filters['account_id'])) {
            $base->where('account_id', $filters['account_id']);
        }
        if (! empty($filters['category_id'])) {
            $base->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['month'])) {
            $base->whereMonth('transaction_date', $filters['month']);
        }
        if (! empty($filters['year'])) {
            $base->whereYear('transaction_date', $filters['year']);
        }
        if (! empty($filters['date_from'])) {
            $base->whereDate('transaction_date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $base->whereDate('transaction_date', '<=', $filters['date_to']);
        }

        // If filtering by a specific type we still want both totals shown
        $income  = (float) (clone $base)->where('type', 'income')->sum('amount');
        $expense = (float) (clone $base)->where('type', 'expense')->sum('amount');
        $count   = (int) (clone $base)->count();

        if (! empty($filters['type'])) {
            // When a specific type is filtered, set the opposite side to 0
            if ($filters['type'] === 'income') {
                $expense = 0;
            } else {
                $income = 0;
            }
            $count = (int) (clone $base)->where('type', $filters['type'])->count();
        }

        return [
            'total_income'  => $income,
            'total_expense' => $expense,
            'net'           => $income - $expense,
            'count'         => $count,
        ];
    }
}
