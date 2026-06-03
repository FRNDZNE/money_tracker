<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);
        $user  = $request->user();

        $expenseQuery = fn () => Transaction::whereHas('account', fn ($q) => $q->where('user_id', $user->id))
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year);

        // Classification breakdown (need / want / investment)
        $classificationData = $expenseQuery()
            ->selectRaw("classification, SUM(amount) as total")
            ->groupBy('classification')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->classification ?? 'unclassified' => (float) $row->total]);

        $classificationBreakdown = [
            'need'         => $classificationData['need'] ?? 0,
            'want'         => $classificationData['want'] ?? 0,
            'investment'   => $classificationData['investment'] ?? 0,
            'unclassified' => $classificationData['unclassified'] ?? 0,
        ];

        // Category breakdown
        $categoryBreakdown = $expenseQuery()
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($transactions) use ($month, $year) {
                $category = $transactions->first()->category;
                $total    = (float) $transactions->sum('amount');

                // Classification split within this category
                $need       = (float) $transactions->where('classification', 'need')->sum('amount');
                $want       = (float) $transactions->where('classification', 'want')->sum('amount');
                $investment = (float) $transactions->where('classification', 'investment')->sum('amount');

                return [
                    'category_id'   => $category?->id,
                    'category_name' => $category?->name ?? 'Uncategorized',
                    'total'         => $total,
                    'count'         => $transactions->count(),
                    'need'          => $need,
                    'want'          => $want,
                    'investment'    => $investment,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $totalExpense = (float) $expenseQuery()->sum('amount');

        return Inertia::render('Analytics/Index', [
            'classification_breakdown' => $classificationBreakdown,
            'category_breakdown'       => $categoryBreakdown,
            'total_expense'            => $totalExpense,
            'filters'                  => [
                'month' => $month,
                'year'  => $year,
            ],
        ]);
    }
}
