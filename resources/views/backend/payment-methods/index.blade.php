@use('app\Helpers\Helpers')
@use('App\Enums\SymbolPositionEnum')
@extends('backend.layouts.master')
@section('title', __('static.payment_methods.payment_methods'))
@section('content')

    <div class="contentbox bg-transparent">
        <div class="inside p-0">
            <div class="contentbox-title">
                <div class="contentbox-subtitle">
                    <h3>{{ __('static.payment_methods.payment_methods') }}</h3>
                </div>
            </div>
            <div class="row g-sm-4 g-3">
                @forelse ($paymentMethods as $paymentMethod)
                    <div class="col-md-4">
                        <div class="card tab2-card h-100 payment-card" >
                            <div class="card-header">
                                <div class="header-img">
                                    <img src="{{ $paymentMethod['image'] }}" alt="" class="img-fluid">
                                    <div class="header-name">
                                        <h5>{{ @$paymentMethod['title'] }}</h5>

                                    </div>
                                </div>
                                <div class="status-div">
                                    <label for="{{ $paymentMethod['name'] }}">{{ __('static.booking.status') }}:</label>
                                    <div class="editor-space">
                                        <label class="switch">
                                            <input class="form-check-input" type="checkbox" name="status" id=""
                                                value="1" @checked($paymentMethod['status'])
                                                onchange="paymentStatus('{{ $paymentMethod['slug'] }}', this.checked)">
                                            <span class="switch-state"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body" data-bs-toggle="modal"
                            data-bs-target="#paymentModal{{ $paymentMethod['slug'] }}">
                                <ul class="payment-keys">
                                    @foreach ($paymentMethod['fields'] as $fieldKey => $field)
                                        @php
                                            $fieldValue = env(strtoupper($fieldKey));
                                        @endphp
                                        @if ($field['type'] === 'password')
                                            <li>
                                                <i class="ri-key-2-line"></i> {{ $field['label'] }} : <span>
                                                    @if (!empty($fieldValue))
                                                        *****{{ substr($fieldValue, strlen($fieldValue) - 4) }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </span>
                                            </li>
                                        @endif
                                        @if ($field['type'] === 'number')
                                            @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                <li>
                                                    <i class="ri-coin-line"></i> {{ $field['label'] }} :
                                                    <span>{{ Helpers::getDefaultCurrencySymbol() }}{{ @$paymentMethod['processing_fee'] }}</span>
                                                </li>
                                            @else
                                                <li>
                                                    <i class="ri-coin-line"></i> {{ $field['label'] }} :
                                                    <span>{{ @$paymentMethod['processing_fee'] }} {{ Helpers::getDefaultCurrencySymbol() }}</span>
                                                </li>
                                            @endif
                                        @endif
                                        @if ($field['type'] === 'select')
                                            <div class="vertical-left-animate">
                                                <div class="ribbon-wrapper">
                                                    @foreach ($field['options'] as $optionValue => $optionLabel)
                                                        @if (!is_null($fieldValue) && $optionValue == $fieldValue)
                                                            <div class="ribbon ribbon-theme ribbon-bookmark ribbon-orange">
                                                                <span>
                                                                    {{ $optionLabel }}
                                                                </span>
                                                                <i class="ri-bookmark-3-line"></i>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @empty
                    <div>
                        <h4>Payment method not found</h4>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @forelse ($paymentMethods as $paymentMethod)
        <div class="modal fade payment-modal-box" id="paymentModal{{ $paymentMethod['slug'] }}">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title fs-5" id="paymentModalLabel">
                            {{ __('static.edit') }}</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal">
                                <i class="ri-close-line"></i>
                            </button>
                    </div>
                    <form action="{{ route('backend.paymentmethods.update', $paymentMethod['slug']) }}" id=""
                        method="POST">
                        @csrf
                        @method('POST')
                    <div class="modal-body">
                            @foreach ($paymentMethod['fields'] as $fieldKey => $field)
                                @php
                                    $fieldValue = env(strtoupper($fieldKey));
                                @endphp
                                <div class="form-group row">
                                    <label class="col-xxl-4" for="{{ $fieldKey }}">{{ $field['label'] }}</label>
                                    <div class="col-xxl-8">
                                        @if ($field['type'] === 'select')
                                            <select class="form-control select-2" name="{{ $fieldKey }}"
                                                id="{{ $fieldKey }}" data-placeholder="{{ $field['label'] }}">
                                                <option class="select-placeholder" value=""></option>
                                                @foreach ($field['options'] as $optionValue => $optionLabel)
                                                    <option value="{{ $optionValue }}"
                                                        @if (!is_null($fieldValue)) @selected($optionValue == $fieldValue) @endif>
                                                        {{ $optionLabel }}</option>
                                                @endforeach
                                            </select>
                                        @elseif ($field['type'] === 'textarea')
                                            <textarea class="form-control" name="{{ $fieldKey }}" id="{{ $fieldKey }}"
                                                placeholder="{{ $field['label'] }}"></textarea>
                                        @elseif ($field['type'] === 'password')
                                            <input class="form-control" type="password" name="{{ $fieldKey }}"
                                                id="{{ $fieldKey }}" placeholder="{{ $field['label'] }}"
                                                value="{{ Helpers::encryptKey($fieldValue) }}">
                                        @elseif ($field['type'] === 'text')
                                            <input class="form-control" type="text" name="{{ $fieldKey }}"
                                                id="{{ $fieldKey }}" placeholder="{{ $field['label'] }}"
                                                value="{{ @$paymentMethod['title'] }}">
                                        @elseif ($field['type'] === 'number')
                                            <input class="form-control" type="number" name="{{ $fieldKey }}"
                                                id="{{ $fieldKey }}" placeholder="{{ $field['label'] }}"
                                                value="{{ @$paymentMethod['processing_fee'] }}">
                                        @elseif($field['type'] === 'checkbox')
                                            <label class="switch">
                                                <input class="form-check-input" type="checkbox" name="{{ $fieldKey }}"
                                                    id="{{ $fieldKey }}" value="1"
                                                    @if (!empty($paymentMethod['subscription']) && $paymentMethod['subscription'] == 1) checked @endif>
                                                <span class="switch-state"></span>
                                            </label>
                                        @else
                                            <input class="form-control" type="{{ $field['type'] }}"
                                                name="{{ $fieldKey }}" id="{{ $fieldKey }}"
                                                value="{{ Helpers::encryptKey($fieldValue) }}"
                                                placeholder="{{ $field['label'] }}">
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="modal-footer">
                            <button id="submitBtn"
                                class="btn btn-primary spinner-btn">{{ __('static.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <li class="no-notifications">
            <div class="payment">
                <div class="no-data mt-3">
                    <img src="{{ url('/images/no-data.png') }}" alt="">
                    <h6 class="mt-2">{{ __('static.payment_methods.not_found') }}</h6>
                </div>
            </div>
        </li>
    @endforelse
@endsection

@push('js')
    <script>
        (function() {
            "use strict";

            document.addEventListener("DOMContentLoaded", () => {
                const ribbons = document.querySelectorAll('.ribbon');

                ribbons.forEach(ribbon => {
                    const span = ribbon.querySelector('span');
                    if (span && span.textContent.trim() === "Live") {
                        ribbon.classList.add('ribbon-success');
                    }
                });
            });

            function paymentStatus(slug, status) {
                fetch(`{{ url('/backend/payment-methods/status') }}/${slug}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            status: status ? 1 : 0
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toastr.success(data.message);
                        } else {
                            toastr.error(data.error);
                        }
                    })
                    .catch(error => {
                        toastr.error(error.message || "An error occurred");
                    });
            }

            window.paymentStatus = paymentStatus;
        })();
    </script>
@endpush
