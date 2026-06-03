<?php

namespace App\Services;

use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class SavingsGoalService
{
    /**
     * Get all savings goals for the given user.
     */
    public function getAll(User $user): Collection
    {
        return $user->savingsGoals()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Create a new savings goal for the given user.
     */
    public function create(User $user, array $data): SavingsGoal
    {
        return $user->savingsGoals()->create($data);
    }

    /**
     * Update an existing savings goal.
     */
    public function update(SavingsGoal $goal, array $data): SavingsGoal
    {
        $goal->update($data);

        return $goal->fresh();
    }

    /**
     * Add an amount to the current_amount of a savings goal.
     */
    public function topUp(SavingsGoal $goal, float $amount): SavingsGoal
    {
        $goal->increment('current_amount', $amount);

        return $goal->fresh();
    }

    /**
     * Delete a savings goal.
     */
    public function delete(SavingsGoal $goal): void
    {
        $goal->delete();
    }
}
