<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RecurringTransferStatus;
use App\Models\RecurringTransfer;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringTransfer>
 */
class RecurringTransferFactory extends Factory
{
    public function definition(): array
    {
        $recipient = User::factory()->create();

        return [
            'source_id' => Wallet::factory(),
            'recipient_email' => $recipient->email,
            'amount' => $this->faker->randomFloat(2, 1, 100),
            'reason' => $this->faker->sentence(),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'frequency_days' => $this->faker->randomDigit(),
            'last_execution' => $this->faker->date(),
            'status' => RecurringTransferStatus::ACTIVE->value
        ];
    }
}
