@extends('backend.layouts.master')
@section('title', __('static.wallet.wallet'))
@section('content')
    @use('app\Helpers\Helpers')
    @use('app\Enums\RoleEnum')
    <div class="row g-4 wallet-main">
        <form action="{{ route('backend.provider-wallet.creditOrdebit') }}" method="POST" id="providerWalletForm">
            @csrf
            <div class="row g-4 wallet-main mb-4">
                @can('backend.provider_wallet.credit')
                    <div class="col-xxl-4 col-xl-5">
                        <div class="wallet-detail card">
                            <div class="wallet-header">
                                <h4>{{ __('static.wallet.select_provider') }}</h4>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2" for="provider_id">{{ __('static.wallet.select_provider') }}<span>
                                        *</span></label>
                                <div class="col-md-10 error-div select-dropdown">
                                    <select class="select-2 form-control user-dropdown" name="provider_id" id="providerDropdown"
                                        data-placeholder="{{ __('static.wallet.select_provider') }}" required>
                                        <option class="select-placeholder" value=""></option>
                                        @foreach ($providers as $provider)
                                            <option value="{{ $provider->id }}" sub-title="{{ $provider->email }}"
                                                image="{{ $provider->getFirstMedia('image')?->getUrl() }}"
                                                {{ $provider->id == request()->query('provider_id') ? 'selected' : '' }}>
                                                {{ $provider->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                    @error('provider_id')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
                <div class="col-xxl-8 col-xl-7 @cannot('backend.provider_wallet.credit') full-width @endcannot">
                    <div class="wallet-detail card">
                        <div class="wallet-header">
                            <h4>{{ __('static.wallet.wallet') }}</h4>
                        </div>
                        <div class="wallet-detail-content">
                            <div class="wallet-amount">
                                <div class="wallet-icon">
                                    <i data-feather="credit-card"></i>
                                </div>
                                <div>
                                    <div class="form-group row">
                                        <label class="col-md-2" for="name">{{ __('static.wallet.balance') }}</label>
                                        <div class="col-md-10 provider-wallet-col">
                                            @php
                                                $user = Auth::user();
                                                $roleName = $user->roles->pluck('name')?->first();
                                            @endphp
                                            @if ($roleName == RoleEnum::ADMIN || $user->can('backend.provider_wallet.credit'))
                                                <input class="form-control" type="text" id="provider-wallet-balance"
                                                    name="name"
                                                    value="{{ Helpers::getSettings()['general']['default_currency']->symbol }} {{ $balance ?? 0.0 }}"
                                                    readonly='true'>
                                            @elseif ($roleName == RoleEnum::PROVIDER)
                                                <input class="form-control" type="text" id="provider-wallet-balance"
                                                    name="name"
                                                    value="{{ Helpers::getSettings()['general']['default_currency']->symbol }} {{ Auth::user()->providerWallet->balance ?? 0.0 }}"
                                                    readonly='true'>
                                            @endif
                                        </div>
                                    </div>
                                    <h5 class="lh-1">{{ __('static.wallet.balance') }}</h5>
                                </div>
                            </div>
                            @canAny(['backend.provider_wallet.credit', 'backend.provider_wallet.debit'])
                                <div class="wallet-form">
                                    <input type="hidden" class="consumerId" name="consumer_id"
                                        value="{{ request()->provider_id }}">
                                    <input type="hidden" name="type" value="">
                                    <div class="form-group row amount g-xxl-4 g-0">
                                        <div class="col-md-10 error-div">
                                            <div class="input-group mb-3 flex-nowrap">
                                                <span
                                                    class="input-group-text">{{ \App\Helpers\Helpers::getSettings()['general']['default_currency']->symbol }}</span>
                                                <div class="w-100">
                                                    <input class="form-control balance"
                                                        placeholder="{{ __('static.wallet.add_amount') }}" type="number"
                                                        name="balance" id="balanceInput" value="{{ old('balance') }}"
                                                        min="1" required>
                                                    @error('balance')
                                                        <span class="invalid-feedback d-block" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="form-check wallet-box">
                                            <input class="form-check-input" type="checkbox" id="addNoteCheck">
                                            <label class="form-check-label" for="addNoteCheck">{{ __('static.wallet.add_note') }}</label>
                                            <input type="text" class="form-control d-none" id="noteInput" name="note"
                                                placeholder="{{ __('static.wallet.enter_note') }}">
                                        </div>
                                    </div>
                                    <div class="credit-debit-buttons">
                                        @can('backend.provider_wallet.credit')
                                            <button type="submit" class="credit btn btn-success delete spinner-btn delete-btn"
                                                id="creditBtn">
                                                {{ __('static.wallet.credit') }}
                                                <i data-feather="arrow-down-circle"></i>
                                            </button>
                                        @endcan
                                        @can('backend.provider_wallet.debit')
                                            <button type="submit" class="debit btn btn-danger delete spinner-btn delete-btn"
                                                id="debitBtn">
                                                {{ __('static.wallet.debit') }}
                                                <i data-feather="arrow-up-circle"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            @endcanAny
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('Transactions') }}</h5>
                </div>
                <div class="card-body common-table">
                    <div class="provider-transaction-table">
                        <div class="table-responsive">
                            {!! $dataTable->table() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    {!! $dataTable->scripts() !!}
    <script>
        $(document).ready(function() {
            "use strict";

            $("#providerWalletForm").validate();

            $('#providerDropdown').change(function() {
                var providerId = $(this).val();
                var url = "{{ route('backend.get-provider-transactions', '') }}" + "/" + providerId;

                window.history.pushState(null, null, '?provider_id=' + providerId);
                location.reload();
            });

            const balanceInput = () => {
                let creditBtn = $("#creditBtn");
                let debitBtn = $("#debitBtn");
                let balanceInput = parseFloat($("#balanceInput").val());
                let balanceLabel = $("#provider-wallet-balance").val();
                balanceLabel = balanceLabel.replace(/[,]/g, '').replace(/[^\d.]/g, '');
                let isConsumerSelected = $("#providerDropdown").val();
                let disableButtons = (
                    balanceInput <= 0 ||
                    Number.isNaN(balanceInput) ||
                    Number.isNaN(balanceLabel) ||
                    !isConsumerSelected
                );
                creditBtn.prop('disabled', disableButtons || balanceInput <= 0);
                debitBtn.prop('disabled', disableButtons || balanceInput > balanceLabel || balanceLabel <= 0);
            };

            $("#balanceInput").on("input", balanceInput);
            $("#providerDropdown").on("change", balanceInput);


            $(".credit").click(function() {
                $('input[name="type"]').val('credit');
            });

            $(".debit").click(function() {
                $('input[name="type"]').val('debit');
            });
            $('#addNoteCheck').change(function() {
                $('#noteInput').toggleClass('d-none', !this.checked);
            });
        });
    </script>
@endpush
