@use('App\Helpers\Helpers')
@use('App\Enums\SymbolPositionEnum')

@extends('frontend.layout.master')

@section('title', __('frontend::static.bookings.payment'))

@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
  <a class="breadcrumb-item" href="{{route('frontend.home')}}">{{__('frontend::static.bookings.home')}}</a>
  <a class="breadcrumb-item" href="{{route('frontend.cart.index')}}">{{__('frontend::static.bookings.my_cart')}}</a>
  <span class="breadcrumb-item active">{{__('frontend::static.bookings.payment_options')}}</span>
</nav>
@endsection

@section('content')
@php
$paymentMethods = Helpers::getActivePaymentMethods() ?? [];
$symbol = Helpers::getDefaultCurrencySymbol();
@endphp

<!-- Service List Section Start -->
<section class="section-b-space">
  <div class="container-fluid-lg">
    <div class="row">
      <div class="col-xxl-9 col-xl-10 col-12 mx-auto">
        <div class="payment">
          <div class="payment-header">
            <div class="ps-4">
              <h3 class="mb-0 f-w-600">{{__('frontend::static.bookings.select_payment_method')}}</h3>
              <span>{{ count($checkout['services']) }} {{__('frontend::static.bookings.service_in_cart_total_amount')}} 
                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                    {{ $symbol }}{{ Helpers::covertDefaultExchangeRate($checkout['total']['total']) }}
                @else
                    {{ Helpers::covertDefaultExchangeRate($checkout['total']['total']) }} {{ $symbol }}
                @endif
              </span>
            </div>
            <a href="javascript:void(0)" class="back-icon"><i class="iconsax" icon-name="chevron-left"></i></a>
          </div>
          <form action="{{route('frontend.booking.store')}}" method="POST">
            @csrf
            @method('POST')
            <div class="payment-body custom-scroll">
              <div class="payment-options row g-3">
                @auth
                <div class="col-lg-6 col-12">
                  <div class="payment-option" data-radio-id="payment-wallet">
                    <div class="form-check">
                      <input type="radio" id="payment-wallet" name="payment_method"
                        class="form-radio-input" value="wallet" checked>
                      <div class="payment-title">
                        <div class="payment-img">
                          <svg class="payment-icon">
                            <use xlink:href="{{ asset('frontend/images/svg/wallet-icon.svg#wallets') }}"></use>
                        </svg>
                          {{-- <img src="{{ asset('frontend/images/svg/Wallet-icon.svg')}}" alt="feature"
                            class="payment-icon img-fluid"> --}}
                        </div>
                        <div>
                          <h4 class="wallet">{{__('frontend::static.bookings.wallet')}}</h4>
                          <p>{{__('frontend::static.bookings.available_balance')}} 
                            <span>
                              @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                  {{ $symbol }}{{ auth()?->user()?->wallet?->balance }}
                              @else
                                  {{ auth()?->user()?->wallet?->balance }} {{ $symbol }}
                              @endif
                            </span>
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                @endauth
                @if(count($paymentMethods))
                @foreach($paymentMethods as $paymentMethod)
                <div class="col-lg-6 col-12">
                  <div class="payment-option" data-radio-id="payment-{{$paymentMethod['slug']}}">
                    <div class="form-check">
                      <input type="radio" id="payment-{{$paymentMethod['slug']}}" name="payment_method" class="form-radio-input" value="{{$paymentMethod['slug']}}">
                      <div class="payment-title">
                        <div class="payment-img">
                          <img src="{{ $paymentMethod['image'] ?? asset('frontend/images/img-not-found.jpg')}}" alt="{{$paymentMethod['name']}}" class="payment-icon img-fluid">
                        </div>
                        <div>
                          <div>
                            <h4 class="wallet">{{$paymentMethod['name']}}</h4>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
                @endif
              </div>
            </div>
            <div class="payment-footer">
              <button type="submit" class="btn btn-solid payment-btn">
                {{__('frontend::static.bookings.confirm_booking')}}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Service List Section End -->
@endsection


@push('js')
<script>
  $(document).ready(function() {
    $('.payment-option').on('click', function() {
      // Get the radio input associated with the clicked div
      var radioId = $(this).data('radio-id');
      $('#' + radioId).prop('checked', true);
    });

    $('form').on('submit', function(e) {
      var $form = $(this);
      var $submitButton = $form.find('.submit.spinner-btn');
      var $spinner = $submitButton.find('.spinner-border');
      e.preventDefault();
      if ($form.valid()) {
        if ($submitButton.length && $spinner.length) {
          $spinner.show();
          $submitButton.prop('disabled', true);
        }

        $form[0].submit();
      }
    });
  });
</script>
@endpush