<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Report — {{ $monthName }} {{ $year }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 12px; color: #1e293b; padding: 40px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #10b981; padding-bottom: 15px; }
        .header h1 { font-size: 22px; color: #0f172a; margin-bottom: 4px; }
        .header p { font-size: 13px; color: #64748b; }
        .summary-grid { display: flex; gap: 0; margin-bottom: 30px; }
        .summary-card { flex: 1; text-align: center; padding: 15px; border: 1px solid #e2e8f0; }
        .summary-card:first-child { border-radius: 8px 0 0 8px; }
        .summary-card:last-child { border-radius: 0 8px 8px 0; }
        .summary-card .label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 4px; }
        .summary-card .value { font-size: 16px; font-weight: 700; }
        .income { color: #10b981; }
        .expense { color: #ef4444; }
        .net-positive { color: #10b981; }
        .net-negative { color: #ef4444; }
        h2 { font-size: 14px; color: #0f172a; margin-bottom: 10px; border-left: 3px solid #10b981; padding-left: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        th { background: #f8fafc; color: #475569; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px 12px; text-align: left; border-bottom: 2px solid #e2e8f0; }
        td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; }
        tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 10px; }
        .total-row { font-weight: 700; background: #f8fafc; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Monthly Financial Report</h1>
        <p>{{ $monthName }} {{ $year }} — {{ $userName }}</p>
    </div>

    <table>
        <tr>
            <td style="text-align: center; padding: 15px; border: 1px solid #e2e8f0; width: 33%;">
                <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 4px;">Total Income</div>
                <div class="income" style="font-size: 16px; font-weight: 700;">Rp {{ number_format($report['total_income'], 0, ',', '.') }}</div>
            </td>
            <td style="text-align: center; padding: 15px; border: 1px solid #e2e8f0; width: 33%;">
                <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 4px;">Total Expense</div>
                <div class="expense" style="font-size: 16px; font-weight: 700;">Rp {{ number_format($report['total_expense'], 0, ',', '.') }}</div>
            </td>
            <td style="text-align: center; padding: 15px; border: 1px solid #e2e8f0; width: 34%;">
                <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 4px;">Net Cash Flow</div>
                <div class="{{ $report['net'] >= 0 ? 'net-positive' : 'net-negative' }}" style="font-size: 16px; font-weight: 700;">Rp {{ number_format($report['net'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    @if($report['expense_categories']->count() > 0)
    <h2>Expense by Category</h2>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-right">Amount</th>
                <th class="text-center">Transactions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['expense_categories'] as $cat)
            <tr>
                <td>{{ $cat['category_name'] }}</td>
                <td class="text-right">Rp {{ number_format($cat['total'], 0, ',', '.') }}</td>
                <td class="text-center">{{ $cat['count'] }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>Total</td>
                <td class="text-right">Rp {{ number_format($report['total_expense'], 0, ',', '.') }}</td>
                <td class="text-center">{{ $report['expense_categories']->sum('count') }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    @if($report['income_categories']->count() > 0)
    <h2>Income by Category</h2>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-right">Amount</th>
                <th class="text-center">Transactions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['income_categories'] as $cat)
            <tr>
                <td>{{ $cat['category_name'] }}</td>
                <td class="text-right">Rp {{ number_format($cat['total'], 0, ',', '.') }}</td>
                <td class="text-center">{{ $cat['count'] }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>Total</td>
                <td class="text-right">Rp {{ number_format($report['total_income'], 0, ',', '.') }}</td>
                <td class="text-center">{{ $report['income_categories']->sum('count') }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <div class="footer">
        Generated on {{ now()->format('F d, Y H:i') }} — Money Tracker
    </div>
</body>
</html>
