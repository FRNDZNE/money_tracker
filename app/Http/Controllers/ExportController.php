<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyReportExport;
use App\Exports\TransactionsExport;
use App\Services\ReportService;
use App\Services\TransactionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly ReportService $reportService,
    ) {}

    /**
     * Export filtered transactions as Excel.
     */
    public function transactionsExcel(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['type', 'account_id', 'category_id', 'month', 'year', 'date_from', 'date_to']);
        $transactions = $this->transactionService->getFiltered($request->user(), $filters);

        $filename = 'transactions_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new TransactionsExport($transactions), $filename);
    }

    /**
     * Export monthly report as Excel.
     */
    public function monthlyReportExcel(Request $request): BinaryFileResponse
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $report   = $this->reportService->getMonthlyReport($request->user(), $month, $year);
        $filename = 'monthly_report_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.xlsx';

        return Excel::download(new MonthlyReportExport($report), $filename);
    }

    /**
     * Export monthly report as PDF.
     */
    public function monthlyReportPdf(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $report   = $this->reportService->getMonthlyReport($request->user(), $month, $year);
        $filename = 'monthly_report_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf';

        $monthName = date('F', mktime(0, 0, 0, $month, 1));

        $pdf = Pdf::loadView('exports.monthly-report', [
            'report'    => $report,
            'monthName' => $monthName,
            'year'      => $year,
            'userName'  => $request->user()->name,
        ]);

        return $pdf->download($filename);
    }
}
