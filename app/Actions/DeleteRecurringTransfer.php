<?php

namespace App\Actions;

use App\Enums\RecurringTransferStatus;
use App\Exceptions\InsufficientBalance;
use App\Exceptions\UnauthorizedRecurringTransfer;
use App\Http\Requests\Api\V1\CreateRecurringTransferRequest;
use App\Models\RecurringTransfer;
use App\Models\User;

readonly class DeleteRecurringTransfer
{

    public function execute(RecurringTransfer $recurringTransfer, User $user): bool
    {
        if($recurringTransfer->source_id !== $user->getKey()){
            throw new UnauthorizedRecurringTransfer(userId: $user->getKey(), ownerId: $recurringTransfer->source_id);
        }

        try{
            $recurringTransfer->delete();

            return true;
        }catch (\Exception $e){
            return false;
        }
    }
}
