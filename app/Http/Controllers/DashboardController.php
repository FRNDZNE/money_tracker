<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $now = now();

        return Inertia::render('Dashboard', [
            'summary' => $this->dashboardService->getSummary($user),
            'accountDistribution' => $this->dashboardService->getAccountDistribution($user),
            'expenseByCategory' => $this->dashboardService->getExpenseByCategory($user, $now->month, $now->year),
            'spendingClassification' => $this->dashboardService->getSpendingClassification($user, $now->month, $now->year),
            'cashFlowTrend' => $this->dashboardService->getCashFlowTrend($user),
            'recentTransactions' => $this->dashboardService->getRecentTransactions($user),
        ]);
    }
}
