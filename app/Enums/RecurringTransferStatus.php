<?php

namespace App\Enums;

enum RecurringTransferStatus: string
{
    case ACTIVE = 'active';
    case FAILED = 'failed';
    case COMPLETED = 'completed';
}
