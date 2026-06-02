<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __construct(
        private readonly AccountService $accountService,
    ) {}

    public function index(Request $request): Response
    {
        $accounts = $this->accountService->getAll($request->user());

        $accountsWithBalance = $accounts->map(fn (Account $account) => [
            'id' => $account->id,
            'name' => $account->name,
            'type' => $account->type,
            'initial_balance' => (float) $account->initial_balance,
            'balance' => $this->accountService->getBalance($account),
            'is_active' => $account->is_active,
        ]);

        return Inertia::render('Accounts/Index', [
            'accounts' => $accountsWithBalance,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Accounts/Create');
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $this->accountService->create($request->user(), $request->validated());

        return redirect()->route('accounts.index')
            ->with('success', 'Account created successfully.');
    }

    public function edit(Account $account): Response
    {
        abort_if($account->user_id !== auth()->id(), 403);

        return Inertia::render('Accounts/Edit', [
            'account' => $account,
        ]);
    }

    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        abort_if($account->user_id !== auth()->id(), 403);

        $this->accountService->update($account, $request->validated());

        return redirect()->route('accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        abort_if($account->user_id !== auth()->id(), 403);

        $this->accountService->delete($account);

        return redirect()->route('accounts.index')
            ->with('success', 'Account deleted successfully.');
    }
}
