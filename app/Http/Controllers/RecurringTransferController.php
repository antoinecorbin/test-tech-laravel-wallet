<?php

namespace App\Http\Controllers;

use App\Actions\CreateRecurringTransfer;
use App\Actions\DeleteRecurringTransfer;
use App\Exceptions\InsufficientBalance;
use App\Exceptions\UnauthorizedRecurringTransfer;
use App\Http\Requests\Api\V1\CreateRecurringTransferRequest;
use App\Models\RecurringTransfer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class RecurringTransferController
{

    public function __construct(
        private readonly CreateRecurringTransfer $createRecurringTransfer,
        private readonly DeleteRecurringTransfer $deleteRecurringTransfer
    )
    {
    }

    public function index(Request $request): View
    {
        $recurringTransfers = $request->user()
            ->recurringTransfers()
            ->latest()
            ->orderBy('start_date')
            ->get();
        $balance = $request->user()->wallet->balance;

        return view('recurring-transfers.index', compact('recurringTransfers', 'balance'));
    }

    public function store(CreateRecurringTransferRequest $request): RedirectResponse
    {
        try {
            $this->createRecurringTransfer->execute(request: $request, user: $request->user());

            return redirect()
                ->back()
                ->with('recurring-transfer-status', 'created');
        } catch (InsufficientBalance $e) {
            return redirect()
                ->back()
                ->with('recurring-transfer-status', 'insufficient-balance');
        }
    }

    public function destroy(RecurringTransfer $recurringTransfer, Request $request)
    {
        try{
            $this->deleteRecurringTransfer->execute(recurringTransfer: $recurringTransfer, user: $request->user());

            return redirect()
                ->back()
                ->with('recurring-transfer-status', 'deleted');
        }catch (UnauthorizedRecurringTransfer $e){
            return redirect()
                ->back()
                ->with('recurring-transfer-status', 'delete-unauthorized');
        }
    }
}
