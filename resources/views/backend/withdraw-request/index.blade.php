@extends('backend.layouts.master')

@section('title', __('static.withdraw.withdraw_requests'))

@section('content')
    @use('app\Helpers\Helpers')
    @cannot('backend.withdraw_request.action')
        <div class="row g-4 wallet-main mb-4">
            <div class="col-xxl-8 col-xl-7 col-sm-6">
                <div class="wallet-detail card">
                    <div class="wallet-detail-content">
                        <div class="wallet-amount">
                            <div class="wallet-icon">
                                <img src="{{ asset('admin/images/svg/Wallet-icon.svg') }}">
                            </div>
                            <div>
                                <div class="form-group row amount">
                                    <label class="col-md-2"
                                        for="{{ __('static.wallet.balance') }}">{{ __('static.wallet.balance') }}<span>
                                            *</span></label>
                                    <div class="col-md-10">
                                        <input class="form-control balance" type="text" id="provider-wallet-balance"
                                            name="name"
                                            value="{{ \App\Helpers\Helpers::getSettings()['general']['default_currency']->symbol }}{{ Auth::user()->providerWallet->balance ?? 0.0 }}"
                                            min="1" readonly>
                                        @error('balance')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <span id="balance-error" class="text-danger mt-1"></span>
                                </div>
                                <h5 class="lh-1">{{ __('static.wallet.pending_balance') }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-xl-5 col-sm-6">
                <div class="wallet-detail card p-4">
                    <div class="d-flex align-items-center gap-3">

                        <div class="send-req">
                            <button type="submit" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                                <div class="withdraw-icon btn btn-primary"></div>
                                {{ __('static.withdraw.send_withdraw_request') }}
                            </button>
                        </div>
                    </div>
                    <div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body text-start p-3">
                                    <div id="form-errors" class="alert alert-danger d-none"></div>
                                    <form method="POST" action="{{ route('backend.withdraw-request.store') }}"
                                        id="withdrawRequestForm">
                                        @csrf
                                        <div class="form-group row">
                                            <label for="amount"
                                                class="col-12">{{ __('static.withdraw.enter_amount') }}</label>
                                            <div class="col-12">
                                                <input class="form-control" type="number" id="amount" name="amount"
                                                    placeholder="{{ __('static.withdraw.enter_amount') }}"
                                                    value="{{ old('amount') }}" required>
                                                @error('amount')
                                                    <span class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <input type="hidden" name="provider_id" value="{{ Auth::user()->id }}">
                                        <div class="form-group row">
                                            <label for="payment_type"
                                                class="col-12">{{ __('static.withdraw.payment_type') }}</label>
                                            <div class="col-12 error-div select-dropdown">
                                                <select class="select-2 form-control" id="payment_type" name="payment_type"
                                                    data-placeholder="{{ __('static.withdraw.select_payment') }}" required>
                                                    <option class="select-placeholder" value=""></option>
                                                    @foreach (['bank' => 'Bank', 'paypal' => 'Paypal'] as $key => $option)
                                                        <option class="option" value="{{ $key }}"
                                                            @if (old('payment_type')) selected @endif>
                                                            {{ $option }}</option>
                                                    @endforeach
                                                </select>
                                                @error('payment_type')
                                                    <span class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class = "form-group row">
                                            <label for="message" class="col-12">{{ __('static.withdraw.message') }}<span>
                                                    *</span></label>
                                            <div class="col-12">
                                                <textarea class = "form-control" id="message" placeholder="{{ __('static.withdraw.enter_message') }}" rows="4"
                                                    name="message" cols="50" required>{{ old('message') }}</textarea>
                                                @error('message')
                                                    <span class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class = "form-group row">
                                            <div class="col-12 text-end">
                                                <button id='submitBtn' type="submit"
                                                    class="btn btn-primary text-end spinner-btn delete-btn">{{ __('static.confirm') }}</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcannot

    <div class="row g-sm-4 g-3">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5>{{ __('static.withdraw.withdraw_requests') }}</h5>
            </div>
            <div class="card-body common-table">
                <div class="withdraw-request-table">
                    <div class="table-responsive">
                        {!! $dataTable->table() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    {!! $dataTable->scripts() !!}
    <script>
        (function($) {
            "use strict";

            $(document).ready(function() {
                $("#withdrawRequestForm").validate();
            });

        })(jQuery);
    </script>
@endpush
