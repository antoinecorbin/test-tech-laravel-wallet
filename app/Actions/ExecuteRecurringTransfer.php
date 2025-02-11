<?php

namespace App\Actions;

use App\Enums\RecurringTransferStatus;
use App\Exceptions\InsufficientBalance;
use App\Http\Requests\Api\V1\CreateRecurringTransferRequest;
use App\Models\RecurringTransfer;
use App\Models\User;
use App\Notifications\TransferFailed;

readonly class ExecuteRecurringTransfer
{
    public function __construct(private PerformWalletTransfer $performWalletTransfer)
    {
    }

    /**
     * @throws InsufficientBalance
     */
    public function execute(RecurringTransfer $recurringTransfer): RecurringTransfer
    {
        try{
            $recipient = User::where('email', $recurringTransfer->recipient_email)->firstOrFail();

            $this->performWalletTransfer->execute(
                sender: $recurringTransfer->source->user,
                recipient: $recipient,
                amount: $recurringTransfer->amount,
                reason: $recurringTransfer->reason
            );

            return $recurringTransfer;
        }catch (InsufficientBalance $e){
            $recurringTransfer->source->user->notify(new TransferFailed());
            $recurringTransfer->updateQuietly([
                'status' => RecurringTransferStatus::FAILED->value,
            ]);

            throw $e;
        }
    }
}
