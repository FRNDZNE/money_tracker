<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Budget;
use App\Services\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function __construct(
        private readonly BudgetService $budgetService,
    ) {}

    public function index(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $budgets    = $this->budgetService->getForUserAndMonth($request->user(), $month, $year);
        $categories = $this->budgetService->getEligibleCategories($request->user(), $month, $year);

        return Inertia::render('Budgets/Index', [
            'budgets'    => $budgets->map(fn (Budget $b) => [
                'id'            => $b->id,
                'category_id'   => $b->category_id,
                'category_name' => $b->category?->name,
                'month'         => $b->month,
                'year'          => $b->year,
                'amount'        => (float) $b->amount,
                'spent'         => $b->spent,
            ]),
            'categories' => $categories->map(fn ($c) => [
                'id'   => $c->id,
                'name' => $c->name,
            ]),
            'filters' => [
                'month' => $month,
                'year'  => $year,
            ],
        ]);
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->budgetService->upsert(
            $request->user(),
            $data['category_id'],
            $data['month'],
            $data['year'],
            $data['amount'],
        );

        return redirect()->back()->with('success', 'Budget saved successfully.');
    }

    public function update(UpdateBudgetRequest $request, Budget $budget): RedirectResponse
    {
        abort_if($budget->user_id !== auth()->id(), 403);

        $this->budgetService->upsert(
            $request->user(),
            $budget->category_id,
            $budget->month,
            $budget->year,
            $request->validated('amount'),
        );

        return redirect()->back()->with('success', 'Budget updated successfully.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        abort_if($budget->user_id !== auth()->id(), 403);

        $this->budgetService->delete($budget);

        return redirect()->back()->with('success', 'Budget removed.');
    }
}
