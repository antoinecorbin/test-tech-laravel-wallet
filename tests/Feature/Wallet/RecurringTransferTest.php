<?php

use App\Console\Kernel;
use App\Enums\RecurringTransferStatus;
use App\Http\Controllers\Api\V1\RecurringTransferController;
use App\Models\RecurringTransfer;
use App\Models\User;
use App\Notifications\TransferFailed;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Notification;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function(){
    $this->sender = $sender = User::factory()->create();
    $sender->wallet->update(['balance' => 10_000]);

    $this->recipient = User::factory()->create();
});

test('user can create recurring transfer', function (){
    actingAs($this->sender);

    $response = postJson(action([RecurringTransferController::class, 'store']), [
        'recipient_email' => $this->recipient->email,
        'amount' => 10.00,
        'reason' => 'Monthly Subscription',
        'frequency_days' => 30,
        'start_date' => now(),
        'end_date' => now()->addYear()
    ]);

    $response->assertCreated();
    expect($response->json())
        ->toHaveKeys(['id', 'recipient_email', 'amount'])
        ->recipient_email->toBe($this->recipient->email)
        ->amount->toBe(1000)
        ->status->toBe(RecurringTransferStatus::ACTIVE->value);
});

test('validation rules when creating recurring transfer', function(){
    actingAs($this->sender);

    $response = postJson(action([RecurringTransferController::class, 'store']), []);

    $response->assertJsonValidationErrors([
        'recipient_email',
        'amount',
        'reason',
        'frequency_days',
        'start_date',
        'end_date',
    ]);
});
test('start date must be after or equal to now', function(){
    actingAs($this->sender);

    $response = postJson(action([RecurringTransferController::class, 'store']), [
        'recipient_email' => $this->recipient->email,
        'amount' => 10.00,
        'reason' => 'Monthly Subscription',
        'frequency_days' => 30,
        'start_date' => now()->subDay(),
        'end_date' => now()->addYear()
    ]);

    $response->assertJsonValidationErrors(['start_date']);
});
test('end date mist be after start date', function (){
    actingAs($this->sender);

    $response = postJson(action([RecurringTransferController::class, 'store']), [
        'recipient_email' => $this->recipient->email,
        'amount' => 10.00,
        'reason' => 'Monthly Subscription',
        'frequency_days' => 30,
        'start_date' => now(),
        'end_date' => now()->subDay()
    ]);

    $response->assertJsonValidationErrors(['end_date']);
});
test('user cannot create recurring transfer to themselves', function(){
    actingAs($this->sender);

    $response = postJson(action([RecurringTransferController::class, 'store']), [
        'recipient_email' => $this->sender->email,
        'amount' => 10.00,
        'reason' => 'Monthly Subscription',
        'frequency_days' => 30,
        'start_date' => now(),
        'end_date' => now()->addYear()
    ]);

    $response->assertJsonValidationErrors(['recipient_email']);
    $this->assertDatabaseMissing('recurring_transfers');
});

test('first transaction is executed immediately after recurring transfer creation', function (){
    actingAs($this->sender);

    $response = postJson(action([RecurringTransferController::class, 'store']), [
        'recipient_email' => $this->recipient->email,
        'amount' => 10.00,
        'reason' => 'Monthly Subscription',
        'frequency_days' => 30,
        'start_date' => now(),
        'end_date' => now()->addYear()
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('wallet_transfers', [
        'source_id' => $this->sender->getKey(),
        'target_id' => $this->recipient->getKey(),
        'amount' => 1000,
    ]);
});

test('transfer fails after creation when user has insufficient balance', function(){
    Notification::fake();

    actingAs($this->sender);

    $this->sender->update(['balance' => 500]);

    $response = postJson(action([RecurringTransferController::class, 'store']), [
        'recipient_email' => $this->recipient->email,
        'amount' => 1000,
        'reason' => 'Monthly Subscription',
        'frequency_days' => 30,
        'start_date' => now(),
        'end_date' => now()->addYear()
    ]);

    $response->assertStatus(422);
    Notification::assertSentTo($this->sender, TransferFailed::class);

    $recurringTransferLatest = RecurringTransfer::latest()->first();
    expect($recurringTransferLatest->status)->toBe(RecurringTransferStatus::FAILED);
});

test('user can delete recurring transfer', function(){
    $recurringTransfer = RecurringTransfer::factory()->create([
        'source_id' => $this->sender->wallet->getKey(),
    ]);

    actingAs($this->sender);

    $response = deleteJson(action([RecurringTransferController::class, 'destroy'], $recurringTransfer));

    $response->assertNoContent();
    $this->assertDatabaseMissing('recurring_transfers', ['id' => $recurringTransfer->getKey()]);
});
test('user cannot delete another users recurring transfer', function(){
    $otherUser = User::factory()->create();

    $recurringTransfer = RecurringTransfer::factory()->create([
        'source_id' => $otherUser->wallet->getKey(),
    ]);

    actingAs($this->sender);

    $response = deleteJson(action([RecurringTransferController::class, 'destroy'], $recurringTransfer));
    $response->assertForbidden();
    $this->assertDatabaseHas('recurring_transfers', ['id' => $recurringTransfer->getKey()]);
});

test('recurring transfers are executed at scheduled time', function(){
    RecurringTransfer::factory()->create([
        'source_id' => $this->sender->wallet->getKey(),
        'recipient_email' => $this->recipient->email,
        'amount' => 1000,
        'frequency_days' => 30,
        'status' => RecurringTransferStatus::ACTIVE,
        'last_execution' => now()->subDays(30),
        'start_date' => now()->subDays(30),
        'end_date' => now()->addYear()
    ]);

    artisan('recurring-transfer:execution');

    expect($this->sender->wallet->fresh()->balance)->toBe(9000);
    $this->assertDatabaseHas('wallet_transfers', [
        'source_id' => $this->sender->wallet->getKey(),
        'target_id' => $this->recipient->getKey(),
        'amount' => 1000,
    ]);
});
test('recurring transfers are not executed after end date', function (){
    RecurringTransfer::factory()->create([
        'source_id' => $this->sender->wallet->getKey(),
        'recipient_email' => $this->recipient->email,
        'amount' => 1000,
        'frequency_days' => 30,
        'status' => RecurringTransferStatus::ACTIVE,
        'last_execution' => now()->subDays(30),
        'start_date' => now()->subDays(30),
        'end_date' => now()->subDay()
    ]);

    artisan('recurring-transfer:execution');

    expect($this->sender->wallet->fresh()->balance)->toBe(10_000);
});
test('recurring transfer execution fails and notify when insufficient balance duing cron', function(){
    Notification::fake();

    actingAs($this->sender);

    RecurringTransfer::factory()->create([
        'source_id' => $this->sender->wallet->getKey(),
        'recipient_email' => $this->recipient->email,
        'amount' => 11_000,
        'frequency_days' => 30,
        'status' => RecurringTransferStatus::ACTIVE,
        'last_execution' => now()->subDays(30),
        'start_date' => now()->subDays(30),
        'end_date' => now()->addYear()
    ]);

    artisan('recurring-transfer:execution');

    Notification::assertSentTo($this->sender, TransferFailed::class);
    expect($this->sender->wallet->fresh()->balance)->toBe(10_000);
});
test( 'recurring transfer command is scheduled at 2am', function(){
    $kernel = app(Kernel::class);
    $schedule = app(Schedule::class);

    $reflection = new ReflectionClass($kernel);
    $method = $reflection->getMethod('schedule');
    $method->invoke($kernel, $schedule);

    $events = collect($schedule->events())->filter(
        fn(Event $event) => str_contains($event->command, 'recurring-transfer:execution')
    );

    expect($events->count())->toBe(1);

    $event = $events->first();
    expect($event->expression)->toBe('0 2 * * *');
});

test('user can list their recurring transfers', function(){
    RecurringTransfer::factory()->create([
        'source_id' => $this->sender->wallet->getKey(),
    ]);

    actingAs($this->sender);
    $response = getJson(action([RecurringTransferController::class, 'index']));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonStructure([
            '*' => [
                'id',
                'recipient_email',
                'amount',
                'reason',
                'frequency_days',
                'status'
            ]
        ]);
});
