@push('css')
<link rel="stylesheet" href="{{ asset('frontend/css/vendors/xdshoft-datetimerpicker/xdsoft-picker.css') }}" />
@endpush

<div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
<h3>{{__('frontend::static.bookings.select_service_delivery')}}</h3>
<a class="add-location text-underline" data-bs-toggle="modal" data-bs-target="#locationModal">
  + {{__('frontend::static.bookings.add_new_address')}}
</a>
</div>

<div class="row g-sm-4 g-3">
  @php
    $addresses = auth()?->user()?->addresses()?->with('country:id,name','state:id,name')?->get()->toArray() ?? session('addresses', []);
  @endphp
  @forelse ($addresses as $serviceAddress)
  <div class="col-xl-6 col-lg-12 col-md-6">
    <div class="card delivery-location">
        <div class="location-header">
          <div class="d-flex align-items-center gap-sm-3 gap-2">
            <div class="location-icon">
              <svg class="active-icon">
                <use xlink:href="{{ asset('frontend/images/svg/profile-svg.svg#home') }}"></use>
              </svg>
            </div>
            <div class="name">
              <h4>{{ $serviceAddress['alternative_name'] ?? auth()?->user()?->name }}</h4>
              <span>({{ $serviceAddress['code'] ?? auth()?->user()?->code }}) {{ $serviceAddress['alternative_phone'] ?? auth()?->user()?->phone }}</span>
            </div>
          </div>
          <span class="badge primary-light-badge">{{ $serviceAddress['type'] }}</span>
        </div>
        <div class="address">
          <label>
          {{__('frontend::static.bookings.address')}}
          </label>
          <p>{{ $serviceAddress['address'] }}
            ,{{ $serviceAddress['state']['name'] ?? $serviceAddress?->state?->name }}
            -
            {{ $serviceAddress['postal_code'] ?? $serviceAddress?->postal_code }},
            {{ $serviceAddress['country']['name'] ?? $serviceAddress?->country?->name }}
          </p>
        </div> 
        <div class="address-bottom-box">
          <div class="action">
            <input class="radio address-select" type="radio" value="{{ $serviceAddress['id'] ?? null }}" name="{{$name ?? 'address_id'}}">
            <button type="button" type="button" class="btn select-btn btn-outline">{{__('frontend::static.bookings.select_this')}}</button>
          </div>
        </div>
    </div>
  </div>
  @empty
  <div class="col-12">
    <div class="no-data-found">
      <p> {{__('frontend::static.bookings.address_not_found')}}</p>
    </div>
  </div>
  @endforelse
</div>

@push('js')
    <!-- Booking Form js -->
    {{-- <script src="{{ asset('frontend/js/form-wizard.js/booking-form.js') }}"></script> --}}

    <script src="{{ asset('frontend/js/xdshoft-datetimerpicker/xdsoft-picker.js') }}"></script>
    <script src="{{ asset('frontend/js/xdshoft-datetimerpicker/xdsoft-picker-2.js') }}"></script>

    <script>
      $('.datetimepicker-slot').datetimepicker('destroy');
      $('.datetimepicker-slot').datetimepicker({
          inline: true,
      });
    </script>
@endpush