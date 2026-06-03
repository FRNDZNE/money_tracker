<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    public function index(Request $request): Response
    {
        $year = (int) $request->get('year', now()->year);

        $summaries      = $this->reportService->getMonthlySummaries($request->user(), $year);
        $availableYears = $this->reportService->getAvailableYears($request->user());

        return Inertia::render('Reports/Index', [
            'summaries'      => $summaries,
            'available_years' => $availableYears,
            'filters'        => [
                'year' => $year,
            ],
        ]);
    }

    public function monthly(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $report         = $this->reportService->getMonthlyReport($request->user(), $month, $year);
        $availableYears = $this->reportService->getAvailableYears($request->user());

        return Inertia::render('Reports/Monthly', [
            'report'         => $report,
            'available_years' => $availableYears,
            'filters'        => [
                'month' => $month,
                'year'  => $year,
            ],
        ]);
    }
}
