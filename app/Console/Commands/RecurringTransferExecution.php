<?php

namespace App\Console\Commands;

use App\Actions\ExecuteRecurringTransfer;
use App\Enums\RecurringTransferStatus;
use App\Models\RecurringTransfer;
use Illuminate\Console\Command;

class RecurringTransferExecution extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring-transfer:execution';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute recurring transfers';

    /**
     * Execute the console command.
     */
    public function handle(ExecuteRecurringTransfer $executeRecurringTransfer)
    {
        RecurringTransfer::whereNotIn('status', [RecurringTransferStatus::COMPLETED->value])
            ->where('end_date', '>=' , now())
            ->where(function ($query){
                $query->whereNull('last_execution')
                    ->orWhere(function ($query){
                        $query->whereRaw("datetime(last_execution, '+' || frequency_days || ' days') <= ?", [now()]);
                    });
            })->each(function ($recurringTransfer) use($executeRecurringTransfer) {
                try{
                    $executeRecurringTransfer->execute(recurringTransfer: $recurringTransfer);
                    $recurringTransfer->updateQuietly(['last_execution' => now()]);
                }catch (\Exception $exception){}
            });
    }
}
