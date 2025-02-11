<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateRecurringTransfer;
use App\Actions\DeleteRecurringTransfer;
use App\Exceptions\InsufficientBalance;
use App\Exceptions\UnauthorizedRecurringTransfer;
use App\Http\Requests\Api\V1\CreateRecurringTransferRequest;
use App\Models\RecurringTransfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecurringTransferController
{

    public function __construct(
        private readonly CreateRecurringTransfer $createRecurringTransfer,
        private readonly DeleteRecurringTransfer $deleteRecurringTransfer
    )
    {
    }

    public function index(Request $request): JsonResponse
    {
        $recurringTransfers = $request->user()
            ->recurringTransfers()
            ->latest()
            ->get();

        return response()->json($recurringTransfers);
    }

    public function store(CreateRecurringTransferRequest $request): JsonResponse
    {
        try{
            $recurringTransfer = $this->createRecurringTransfer->execute(request: $request, user: $request->user());

            return response()->json($recurringTransfer, 201);
        }catch (InsufficientBalance $e){
            return response()->json($e->getMessage(), 422);
        }
    }

    public function destroy(RecurringTransfer $recurringTransfer, Request $request)
    {
        try{
            $this->deleteRecurringTransfer->execute(recurringTransfer: $recurringTransfer, user: $request->user());

            return response()->noContent();
        }catch (UnauthorizedRecurringTransfer $e){
            return response()->json($e->getMessage(), 403);
        }
    }
}
