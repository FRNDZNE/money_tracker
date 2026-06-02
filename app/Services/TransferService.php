<?php

namespace App\Services;

use App\Models\Transfer;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransferService
{
    /**
     * Get paginated transfers for the given user.
     */
    public function getPaginated(User $user): LengthAwarePaginator
    {
        return Transfer::whereHas('fromAccount', fn ($q) => $q->where('user_id', $user->id))
            ->with(['fromAccount', 'toAccount'])
            ->orderBy('transfer_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * Create a new transfer.
     */
    public function create(array $data): Transfer
    {
        return Transfer::create($data);
    }

    /**
     * Update an existing transfer.
     */
    public function update(Transfer $transfer, array $data): Transfer
    {
        $transfer->update($data);

        return $transfer->fresh();
    }

    /**
     * Delete a transfer.
     */
    public function delete(Transfer $transfer): void
    {
        $transfer->delete();
    }
}
