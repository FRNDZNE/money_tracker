<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AccountService
{
    /**
     * Get all accounts for the given user.
     */
    public function getAll(User $user): Collection
    {
        return $user->accounts()->orderBy('name')->get();
    }

    /**
     * Get only active accounts for the given user.
     */
    public function getAllActive(User $user): Collection
    {
        return $user->accounts()->where('is_active', true)->orderBy('name')->get();
    }

    /**
     * Create a new account for the given user.
     */
    public function create(User $user, array $data): Account
    {
        return $user->accounts()->create($data);
    }

    /**
     * Update an existing account.
     */
    public function update(Account $account, array $data): Account
    {
        $account->update($data);

        return $account->fresh();
    }

    /**
     * Delete an account.
     */
    public function delete(Account $account): void
    {
        $account->delete();
    }

    /**
     * Calculate the current balance of an account.
     *
     * balance = initial_balance + total_income - total_expense + transfer_in - transfer_out
     */
    public function getBalance(Account $account): float
    {
        $income = (float) $account->transactions()->where('type', 'income')->sum('amount');
        $expense = (float) $account->transactions()->where('type', 'expense')->sum('amount');
        $transferIn = (float) $account->transfersIn()->sum('amount');
        $transferOut = (float) $account->transfersOut()->sum('amount');

        return (float) $account->initial_balance + $income - $expense + $transferIn - $transferOut;
    }

    /**
     * Get the total assets across all active accounts for the given user.
     */
    public function getTotalAssets(User $user): float
    {
        return $user->accounts()
            ->where('is_active', true)
            ->get()
            ->sum(fn (Account $account) => $this->getBalance($account));
    }
}
