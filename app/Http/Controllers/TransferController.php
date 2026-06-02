<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransferRequest;
use App\Http\Requests\UpdateTransferRequest;
use App\Models\Account;
use App\Models\Transfer;
use App\Services\AccountService;
use App\Services\TransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransferController extends Controller
{
    public function __construct(
        private readonly TransferService $transferService,
        private readonly AccountService $accountService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Transfers/Index', [
            'transfers' => $this->transferService->getPaginated($request->user()),
            'accounts' => $this->accountService->getAllActive($request->user()),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Transfers/Create', [
            'accounts' => $this->accountService->getAllActive($request->user()),
        ]);
    }

    public function store(StoreTransferRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Ensure both accounts belong to the authenticated user
        $fromAccount = Account::find($validated['from_account_id']);
        $toAccount = Account::find($validated['to_account_id']);
        abort_if($fromAccount->user_id !== auth()->id() || $toAccount->user_id !== auth()->id(), 403);

        $this->transferService->create($validated);

        return redirect()->route('transfers.index')
            ->with('success', 'Transfer recorded successfully.');
    }

    public function edit(Request $request, Transfer $transfer): Response
    {
        abort_if($transfer->fromAccount->user_id !== auth()->id(), 403);

        return Inertia::render('Transfers/Edit', [
            'transfer' => $transfer->load(['fromAccount', 'toAccount']),
            'accounts' => $this->accountService->getAllActive($request->user()),
        ]);
    }

    public function update(UpdateTransferRequest $request, Transfer $transfer): RedirectResponse
    {
        abort_if($transfer->fromAccount->user_id !== auth()->id(), 403);

        $validated = $request->validated();

        // Ensure both accounts belong to the authenticated user
        $fromAccount = Account::find($validated['from_account_id']);
        $toAccount = Account::find($validated['to_account_id']);
        abort_if($fromAccount->user_id !== auth()->id() || $toAccount->user_id !== auth()->id(), 403);

        $this->transferService->update($transfer, $validated);

        return redirect()->route('transfers.index')
            ->with('success', 'Transfer updated successfully.');
    }

    public function destroy(Transfer $transfer): RedirectResponse
    {
        abort_if($transfer->fromAccount->user_id !== auth()->id(), 403);

        $this->transferService->delete($transfer);

        return redirect()->route('transfers.index')
            ->with('success', 'Transfer deleted successfully.');
    }
}
