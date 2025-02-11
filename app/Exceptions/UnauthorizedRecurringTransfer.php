<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Wallet;

class UnauthorizedRecurringTransfer extends ApiException
{
    public function __construct(public int $userId, public int $ownerId)
    {
        parent::__construct(message: 'Your are not authorized to delete this recurring transfer.', code: 'UNAUTHORIZED_RECURRING_TRANSFER', status: 403);
    }
}
