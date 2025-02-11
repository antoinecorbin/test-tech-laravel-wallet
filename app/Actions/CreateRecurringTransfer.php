<?php

namespace App\Actions;

use App\Enums\RecurringTransferStatus;
use App\Exceptions\InsufficientBalance;
use App\Http\Requests\Api\V1\CreateRecurringTransferRequest;
use App\Models\RecurringTransfer;
use App\Models\User;

readonly class CreateRecurringTransfer
{
    public function __construct(private PerformWalletTransfer    $performWalletTransfer,
                                private ExecuteRecurringTransfer $executeRecurringTransfer)
    {
    }

    /**
     * @throws InsufficientBalance
     */
    public function execute(CreateRecurringTransferRequest $request, User $user)
    {
        $recurringTransfer = RecurringTransfer::create([
            ...$request->validated(),
            'source_id' => $user->getKey(),
            'amount' => $this->getAmountsInCents(amount: $request->float('amount')),
            'status' => RecurringTransferStatus::ACTIVE->value
        ]);

        $this->executeRecurringTransfer->execute(recurringTransfer: $recurringTransfer);

        return $recurringTransfer;
    }

    private function getAmountsInCents(float $amount): int
    {
        return (int) ceil(num: $amount * 100);
    }
}
