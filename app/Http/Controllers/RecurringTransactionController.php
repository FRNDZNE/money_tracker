<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Http\Requests\UpdateRecurringTransactionRequest;
use App\Models\Account;
use App\Models\RecurringTransaction;
use App\Services\AccountService;
use App\Services\CategoryService;
use App\Services\RecurringTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecurringTransactionController extends Controller
{
    public function __construct(
        private readonly RecurringTransactionService $recurringTransactionService,
        private readonly AccountService $accountService,
        private readonly CategoryService $categoryService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('RecurringTransactions/Index', [
            'recurringTransactions' => $this->recurringTransactionService->getPaginated($request->user()),
            'accounts' => $this->accountService->getAllActive($request->user()),
            'categories' => $this->categoryService->getAll($request->user()),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('RecurringTransactions/Create', [
            'accounts' => $this->accountService->getAllActive($request->user()),
            'categories' => $this->categoryService->getAll($request->user()),
        ]);
    }

    public function store(StoreRecurringTransactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Ensure the selected account belongs to the authenticated user
        $account = Account::find($validated['account_id']);
        abort_if($account->user_id !== auth()->id(), 403);

        $validated['user_id'] = auth()->id();
        $this->recurringTransactionService->create($validated);

        return redirect()->route('recurring-transactions.index')
            ->with('success', 'Recurring transaction created successfully.');
    }

    public function edit(Request $request, RecurringTransaction $recurringTransaction): Response
    {
        abort_if($recurringTransaction->user_id !== auth()->id(), 403);

        return Inertia::render('RecurringTransactions/Edit', [
            'recurringTransaction' => $recurringTransaction->load(['account', 'category', 'subCategory']),
            'accounts' => $this->accountService->getAllActive($request->user()),
            'categories' => $this->categoryService->getAll($request->user()),
        ]);
    }

    public function update(UpdateRecurringTransactionRequest $request, RecurringTransaction $recurringTransaction): RedirectResponse
    {
        abort_if($recurringTransaction->user_id !== auth()->id(), 403);

        // Ensure the new account also belongs to the authenticated user
        $account = Account::find($request->validated()['account_id']);
        abort_if($account->user_id !== auth()->id(), 403);

        $this->recurringTransactionService->update($recurringTransaction, $request->validated());

        return redirect()->route('recurring-transactions.index')
            ->with('success', 'Recurring transaction updated successfully.');
    }

    public function destroy(RecurringTransaction $recurringTransaction): RedirectResponse
    {
        abort_if($recurringTransaction->user_id !== auth()->id(), 403);

        $this->recurringTransactionService->delete($recurringTransaction);

        return redirect()->route('recurring-transactions.index')
            ->with('success', 'Recurring transaction deleted successfully.');
    }
}
