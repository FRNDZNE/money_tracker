<?php

namespace App\Services;

use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class RecurringTransactionService
{
    /**
     * Get paginated recurring transactions for the given user.
     */
    public function getPaginated(User $user): LengthAwarePaginator
    {
        return RecurringTransaction::where('user_id', $user->id)
            ->with(['account', 'category', 'subCategory'])
            ->orderBy('next_due_date', 'asc')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * Create a new recurring transaction.
     */
    public function create(array $data): RecurringTransaction
    {
        $data['next_due_date'] = $data['start_date'];

        return RecurringTransaction::create($data);
    }

    /**
     * Update an existing recurring transaction.
     */
    public function update(RecurringTransaction $recurring, array $data): RecurringTransaction
    {
        $recurring->update($data);

        return $recurring->fresh();
    }

    /**
     * Delete a recurring transaction.
     */
    public function delete(RecurringTransaction $recurring): void
    {
        $recurring->delete();
    }

    /**
     * Process all due recurring transactions.
     * Creates actual transactions for each due recurring record and advances the next_due_date.
     */
    public function processDueTransactions(): int
    {
        $today = Carbon::today();
        $count = 0;

        $dueRecurrings = RecurringTransaction::where('is_active', true)
            ->where('next_due_date', '<=', $today)
            ->get();

        foreach ($dueRecurrings as $recurring) {
            // Create the actual transaction
            Transaction::create([
                'account_id' => $recurring->account_id,
                'category_id' => $recurring->category_id,
                'sub_category_id' => $recurring->sub_category_id,
                'type' => $recurring->type,
                'amount' => $recurring->amount,
                'description' => $recurring->description,
                'transaction_date' => $recurring->next_due_date,
                'classification' => $recurring->classification,
            ]);

            $count++;

            // Advance the next_due_date based on frequency
            $nextDate = $this->calculateNextDate($recurring->next_due_date, $recurring->frequency);

            // Deactivate if past end_date
            if ($recurring->end_date && $nextDate->greaterThan($recurring->end_date)) {
                $recurring->update([
                    'is_active' => false,
                    'next_due_date' => $nextDate,
                ]);
            } else {
                $recurring->update([
                    'next_due_date' => $nextDate,
                ]);
            }
        }

        return $count;
    }

    /**
     * Calculate the next due date based on frequency.
     */
    private function calculateNextDate(Carbon|string $currentDate, string $frequency): Carbon
    {
        $date = Carbon::parse($currentDate);

        return match ($frequency) {
            'monthly' => $date->addMonth(),
            default => $date->addMonth(),
        };
    }
}
