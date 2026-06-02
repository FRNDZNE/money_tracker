<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Account;
use App\Models\Transaction;
use App\Services\AccountService;
use App\Services\CategoryService;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly AccountService $accountService,
        private readonly CategoryService $categoryService,
    ) {}

    public function index(Request $request): Response
    {
        $transactions = $this->transactionService->getPaginated(
            $request->user(),
            $request->only(['type', 'account_id', 'category_id', 'month', 'year'])
        );

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'accounts' => $this->accountService->getAllActive($request->user()),
            'categories' => $this->categoryService->getAll($request->user()),
            'filters' => $request->only(['type', 'account_id', 'category_id', 'month', 'year']),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Transactions/Create', [
            'accounts' => $this->accountService->getAllActive($request->user()),
            'categories' => $this->categoryService->getAll($request->user()),
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        // Ensure the selected account belongs to the authenticated user
        $account = Account::find($request->validated()['account_id']);
        abort_if($account->user_id !== auth()->id(), 403);

        $this->transactionService->create($request->validated());

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction created successfully.');
    }

    public function edit(Request $request, Transaction $transaction): Response
    {
        abort_if($transaction->account->user_id !== auth()->id(), 403);

        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction->load(['account', 'category', 'subCategory']),
            'accounts' => $this->accountService->getAllActive($request->user()),
            'categories' => $this->categoryService->getAll($request->user()),
        ]);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        abort_if($transaction->account->user_id !== auth()->id(), 403);

        // Ensure the new account also belongs to the authenticated user
        $account = Account::find($request->validated()['account_id']);
        abort_if($account->user_id !== auth()->id(), 403);

        $this->transactionService->update($transaction, $request->validated());

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        abort_if($transaction->account->user_id !== auth()->id(), 403);

        $this->transactionService->delete($transaction);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }
}
