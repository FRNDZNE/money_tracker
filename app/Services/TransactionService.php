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
}
