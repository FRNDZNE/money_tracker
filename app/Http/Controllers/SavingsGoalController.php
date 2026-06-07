<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSavingsGoalRequest;
use App\Http\Requests\TopUpSavingsGoalRequest;
use App\Http\Requests\UpdateSavingsGoalRequest;
use App\Models\SavingsGoal;
use App\Services\SavingsGoalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SavingsGoalController extends Controller
{
    public function __construct(
        private readonly SavingsGoalService $savingsGoalService,
    ) {}

    public function index(Request $request): Response
    {
        $goals = $this->savingsGoalService->getAll($request->user());

        return Inertia::render('SavingsGoals/Index', [
            'goals' => $goals->map(fn (SavingsGoal $g) => [
                'id'             => $g->id,
                'name'           => $g->name,
                'target_amount'  => (float) $g->target_amount,
                'current_amount' => (float) $g->current_amount,
                'target_date'    => $g->target_date?->toDateString(),
                'progress'       => $g->target_amount > 0
                    ? min(100, round(($g->current_amount / $g->target_amount) * 100, 1))
                    : 0,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('SavingsGoals/Create');
    }

    public function store(StoreSavingsGoalRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['current_amount'] = $data['current_amount'] ?? 0;

        $this->savingsGoalService->create($request->user(), $data);

        return redirect()->route('savings-goals.index')
            ->with('success', 'Savings goal created successfully.');
    }

    public function edit(SavingsGoal $savingsGoal): Response
    {
        abort_if($savingsGoal->user_id !== auth()->id(), 403);

        return Inertia::render('SavingsGoals/Edit', [
            'goal' => [
                'id'             => $savingsGoal->id,
                'name'           => $savingsGoal->name,
                'target_amount'  => (float) $savingsGoal->target_amount,
                'current_amount' => (float) $savingsGoal->current_amount,
                'target_date'    => $savingsGoal->target_date?->toDateString(),
            ],
        ]);
    }

    public function update(UpdateSavingsGoalRequest $request, SavingsGoal $savingsGoal): RedirectResponse
    {
        abort_if($savingsGoal->user_id !== auth()->id(), 403);

        $data = $request->validated();
        $data['current_amount'] = $data['current_amount'] ?? 0;

        $this->savingsGoalService->update($savingsGoal, $data);

        return redirect()->route('savings-goals.index')
            ->with('success', 'Savings goal updated successfully.');
    }

    public function topUp(TopUpSavingsGoalRequest $request, SavingsGoal $savingsGoal): RedirectResponse
    {
        abort_if($savingsGoal->user_id !== auth()->id(), 403);

        $this->savingsGoalService->topUp($savingsGoal, (float) $request->validated('amount'));

        return redirect()->route('savings-goals.index')
            ->with('success', 'Amount added to savings goal.');
    }

    public function destroy(SavingsGoal $savingsGoal): RedirectResponse
    {
        abort_if($savingsGoal->user_id !== auth()->id(), 403);

        $this->savingsGoalService->delete($savingsGoal);

        return redirect()->route('savings-goals.index')
            ->with('success', 'Savings goal deleted.');
    }
}
