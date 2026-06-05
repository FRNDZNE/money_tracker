<?php

namespace App\Http\Controllers;

use App\Services\FinancialInsightService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinancialInsightController extends Controller
{
    public function __construct(
        private readonly FinancialInsightService $insightService,
    ) {}

    public function index(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $insights = $this->insightService->getInsights($request->user(), $month, $year);

        return Inertia::render('Insights/Index', [
            'insights' => $insights,
            'filters'  => [
                'month' => $month,
                'year'  => $year,
            ],
        ]);
    }
}
