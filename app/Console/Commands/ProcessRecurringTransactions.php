<?php

namespace App\Console\Commands;

use App\Services\RecurringTransactionService;
use Illuminate\Console\Command;

class ProcessRecurringTransactions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'recurring:process';

    /**
     * The console command description.
     */
    protected $description = 'Process all due recurring transactions and create actual transactions';

    public function __construct(
        private readonly RecurringTransactionService $recurringTransactionService,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing recurring transactions...');

        $count = $this->recurringTransactionService->processDueTransactions();

        $this->info("Done! {$count} transaction(s) created.");

        return Command::SUCCESS;
    }
}
