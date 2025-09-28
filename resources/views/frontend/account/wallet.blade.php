@use('app\Helpers\Helpers')
@use('App\Enums\SymbolPositionEnum')
@extends('frontend.layout.master')
@push('css')
<!-- datatables css-->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/datatables.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/select-datatables.min.css') }}">
<!-- Flatpicker css -->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/flatpickr/flatpickr.min.css') }}">
@endpush

@section('title', 'Wallet')

@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{ url('/') }}">{{ __('frontend::static.account.home') }}</a>
    <span class="breadcrumb-item active">{{ __('frontend::static.account.wallet') }}</span>
</nav>
@endsection

@section('content')
<!-- Service List Section Start -->
<section class="section-b-space">
    <div class="container-fluid-md">
        <div class="profile-body-wrapper">
            <div class="row">
                @includeIf('frontend.account.sidebar')
                <div class="col-xxl-9 col-xl-8">
                    <button class="filter-btn btn theme-bg-color text-white w-max d-xl-none d-inline-block mb-3">
                        {{ __('frontend::static.account.show_menu') }}
                    </button>
                    <div class="profile-main h-100">
                        <div class="card payment m-0">
                            <div class="card-header">
                                <div class="title-3">
                                    <h3>{{ __('frontend::static.account.my_wallet') }}
                                        @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                            <span class="text-success">{{ Helpers::getDefaultCurrencySymbol() }}{{ auth()?->user()?->wallet?->balance ?? 00 }}</span>
                                        @else
                                            <span class="text-success">{{ auth()?->user()?->wallet?->balance ?? 00 }} {{ Helpers::getDefaultCurrencySymbol() }}</span>
                                        @endif
                                    </h3>
                                </div>
                                <button type="button" class="edit-option text-theme-color" data-bs-toggle="modal"
                                    data-bs-target="#walletModal">
                                    + {{ __('frontend::static.account.add_balance') }}
                                </button>
                            </div>
                            <div class="card-body wallet-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="select-date">
                                            <h4>{{ __('frontend::static.account.transactions') }}</h4>
                                            <div class="date-pick">
                                                <label>{{ __('frontend::static.account.date') }}</label>
                                                <div class="input-group flatpicker-calender">
                                                    <input class="form-control form-control-white" id="range-date"
                                                        type="text" readonly="readonly" name="date"
                                                        placeholder="Select Date">
                                                    <i class="iconsax input-icon" icon-name="calendar-1"></i>
                                                </div>
                                                <button id="filter-btn" class="btn btn-solid">{{ __('frontend::static.account.filter') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="wallet-data wallet-table wallet-data-table custom-scrollbar common-table">
                                            <div class="table-responsive border-0">
                                                {!! $dataTable->table() !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Service List Section End -->

@php
$paymentMethods = Helpers::getActiveOnlinePaymentMethods() ?? [];
@endphp

<!-- add money modal -->
<div class="modal fade wallet-modal" id="walletModal">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{route('frontend.wallet.topUp')}}" method="post" id="topUpForm">
            @csrf
            @method('POST')
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="walletModalLabel">{{ __('frontend::static.account.add_money') }}</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="add-money">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="mb-2">{{ __('frontend::static.account.add_from') }}</label>
                                <div class="position-relative phone-detail">
                                    <i class="iconsax input-icon" icon-name="wallet-open"></i>
                                    <select class="form-select form-select-white select-2" id="payment_method"
                                        name="payment_method"
                                        data-placeholder="{{ __('frontend::static.account.select_payment_gateway') }}">
                                        <option class="select-placeholder" value=""></option>
                                        @foreach($paymentMethods as $payment)
                                        <option value="{{$payment['slug']}}">{{ $payment['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="mb-2">{{ __('frontend::static.account.amount') }}</label>
                                <div class="input-group">
                                    <input type="number" name="amount"
                                        placeholder="{{ __('frontend::static.account.amount') }}"
                                        class="form-control form-control-white order-0 w-100">
                                    <i class="iconsax input-icon" icon-name="money-in"></i>
                                </div>
                                @error('amount')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline"
                        data-bs-dismiss="modal">{{ __('frontend::static.account.cancel') }}</button>
                    <button type="submit" id="addMondayBtn"
                        class="btn btn-solid">{{ __('frontend::static.account.add_money') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')

<!-- datatables js -->
<script src="{{ asset('frontend/js/datatables.min.js') }}"></script>

<!-- flatpickr js -->
<script src="{{ asset('frontend/js/flat-pickr/flatpickr.js') }}"></script>
<script src="{{ asset('frontend/js/flat-pickr/custom-flatpickr.js') }}"></script>
{!! $dataTable->scripts() !!}
<script>
$(function() {
    "use strict";

    flatpickr("#range-date", {
        mode: "range",
        dateFormat: "Y-m-d",
    });

    $("#topUpForm").validate({
        ignore: [],
        rules: {
            "amount": {
                required: true,
                min: 10,
                max: 10000
            },
            "payment_method": "required"
        },
        messages: {
            "amount": {
                required: "Please enter an amount.",
                min: "The minimum amount is 10.",
                max: "The maximum amount is 10000."
            },
            "payment_method": "Please select a payment method."
        }
    });

    $('#payment_method').on('change', function() {
        $(this).valid();
    });

    $('#addMondayBtn').on('click', function() {
        if ($("#topUpForm").valid()) {
            $('#topUpForm').submit();
        }
    });

    $('#filter-btn').on('click', function() {

        const dateRange = $('#range-date').val();
        if (!dateRange) {
            alert("Please select a date range.");
            return;
        }

        const [startDate, endDate] = $('#range-date').val().split(' to ');
        const url = `{{ route('frontend.account.wallet') }}?start_date=${startDate}&end_date=${endDate}`;
                    $('#wallet-data').DataTable().ajax.url(url).load();
                    $('#range-date').val(dateRange);
    });
});
</script>
@endpush