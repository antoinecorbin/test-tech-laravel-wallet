@php use Illuminate\Support\Number; @endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Recurring Transfers') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('recurring-transfer-status') === 'created')
                <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                    <span class="font-medium">@lang('Transfer récurrent créé')</span>
                    <p>Vous venez de créer un transfer récurrent, le premier virement a bien été effecuté.</p>
                </div>
            @elseif (session('recurring-transfer-status') === 'insufficient-balance')
                <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                    <span class="font-medium">@lang('Transfer récurrent créé')</span>
                    <p>Vous venez de créer un transfer récurrent, le premier virement n'as pas été effecuté car vous
                        avez un solde inférieur au montant renseigner dans le transfer récurrent. Veuillez alimentez
                        votre compte.</p>
                </div>
                @elseif (session('recurring-transfer-status') === 'deleted')
                <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                    <span class="font-medium">@lang('Transfer récurrent supprimé')</span>
                    <p>Vous venez de supprimer un transfer récurrent, tous les prochains virement sont supprimés.</p>
                </div>
            @elseif (session('recurring-transfer-status') === 'delete-unauthorized')
                <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                    <span class="font-medium">@lang('Vous ne pouvez pas effectuer cette action')</span>
                    <p>Vous ne pouvez pas supprimer ce transfer récurrent.</p>
                </div>
            @endif

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                    <div class="text-base text-gray-400">@lang('Balance')</div>
                    <div class="flex items-center pt-1">
                        <div class="text-2xl font-bold text-gray-900">
                            {{ Number::currencyCents($balance) }}
                        </div>
                    </div>
                </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                <h2 class="text-xl font-bold mb-6">@lang('Create recurring transfer')</h2>
                <form method="POST" action="{{ route('recurring-transfers.store') }}" class="space-y-4  mb-5">
                    @csrf

                    <div>
                        <x-input-label for="recipient_email" :value="__('Recipient email')"/>
                        <x-text-input id="recipient_email"
                                      class="block mt-1 w-full"
                                      type="email"
                                      name="recipient_email"
                                      :value="old('recipient_email')"
                                      required/>
                        <x-input-error :messages="$errors->get('recipient_email')" class="mt-2"/>
                    </div>
                    <div>
                        <x-input-label for="frequency_days" :value="__('Frequency Days')"/>
                        <x-text-input id="frequency_days"
                                      class="block mt-1 w-full"
                                      type="number"
                                      name="frequency_days"
                                      :value="old('frequency_days')"
                                      required/>
                        <x-input-error :messages="$errors->get('frequency_days')" class="mt-2"/>
                    </div>
                    <div>
                        <x-input-label for="amount" :value="__('Amount (€)')"/>
                        <x-text-input id="amount"
                                      class="block mt-1 w-full"
                                      type="number"
                                      min="0"
                                      step="0.01"
                                      :value="old('amount')"
                                      name="amount"
                                      required/>
                        <x-input-error :messages="$errors->get('amount')" class="mt-2"/>
                    </div>
                    <div>
                        <x-input-label for="reason" :value="__('Reason')"/>
                        <x-text-input id="reason"
                                      class="block mt-1 w-full"
                                      type="text"
                                      :value="old('reason')"
                                      name="reason"
                                      required/>
                        <x-input-error :messages="$errors->get('reason')" class="mt-2"/>
                    </div>
                    <div>
                        <x-input-label for="start_date" :value="__('Start date')"/>
                        <x-text-input id="start_date"
                                      class="block mt-1 w-full"
                                      type="date"
                                      :value="old('start_date')"
                                      name="start_date"
                                      required/>
                        <x-input-error :messages="$errors->get('start_date')" class="mt-2"/>
                    </div>
                    <div>
                        <x-input-label for="start_date" :value="__('End date')"/>
                        <x-text-input id="end_date"
                                      class="block mt-1 w-full"
                                      type="date"
                                      :value="old('end_date')"
                                      name="end_date"
                                      required/>
                        <x-input-error :messages="$errors->get('end_date')" class="mt-2"/>
                    </div>

                    <div class="flex justify-end mt-4">
                        <x-primary-button>
                            {{ __('Create recurring transfer') }}
                        </x-primary-button>
                    </div>
                </form>
                @if($recurringTransfers->isNotEmpty())
                    <table class="w-full text-sm text-left text-gray-500 border border-gray-200">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                @lang('Recipient')
                            </th>
                            <th scope="col" class="px-6 py-3">
                                @lang('Reason')
                            </th>
                            <th scope="col" class="px-6 py-3">
                                @lang('Amount')
                            </th>
                            <th scope="col" class="px-6 py-3">
                                @lang('Frequency')
                            </th>
                            <th scope="col" class="px-6 py-3">
                                @lang('Start Date')
                            </th>
                            <th scope="col" class="px-6 py-3">
                                @lang('End Date')
                            </th>
                            <th scope="col" class="px-6 py-3">
                                @lang('Status')
                            </th>
                            <th scope="col" class="px-6 py-3">
                                @lang('Actions')
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($recurringTransfers as $transfer)
                            <tr class="bg-white border-b">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                    {{$transfer->recipient_email}}
                                </th>
                                <td class="px-6 py-4">
                                    {{$transfer->reason}}
                                </td>
                                <td class="px-6 py-4">
                                    {{ Number::currencyCents($transfer->amount) }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $transfer->frequency_days }} days
                                </td>
                                <td class="px-6 py-4">
                                    {{ $transfer->start_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $transfer->end_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4">
                            <span @class([
    'px-2 py-1 text-xs font-medium rounded-full',
    'bg-green-100 text-green-800' => $transfer->status === \App\Enums\RecurringTransferStatus::ACTIVE,
    'bg-red-100 text-red-800' => $transfer->status === \App\Enums\RecurringTransferStatus::FAILED,
    'bg-gray-100 text-gray-800' => $transfer->status === \App\Enums\RecurringTransferStatus::COMPLETED
])>
                                {{ $transfer->status->value }}
                            </span>
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" action="{{ route('recurring-transfers.destroy', $transfer) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Voulez-vous vraiment supprimer ce transfer récurrent ?')">
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="p-5 rounded-md bg-gray-300">Vous n'avez pas encore de transfer récurrent de créé.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
