@use('app\Helpers\Helpers')
<div class="servicemen-list-box custom-scrollbar">
  <ul class="servicemen-list">
      @forelse ($cartItems as $cartItem)
          <li>
                <div class="servicemen-horizontal-box">
                <button class="btn delete-icon remove-from-cart" type="button" data-id="{{ $cartItem->id }}">
                    <i class="ri-delete-bin-line"></i>
                </button>

                  <div class="top-box">
                  <div class="servicemen-image">

                    <img src="{{ $cartItem?->service?->web_img_thumb_url }}" class="img-fluid" alt="{{ $cartItem?->service?->title }}">
                  </div>
                      <div class="servicemen-details">
                          <h4>{{ $cartItem?->service?->title }}</h4>
                          <ul class="service-list">
                              <li class="time">
                                  <i data-feather="clock"></i>
                                  <span>{{ $cartItem?->service?->duration }} {{ $cartItem?->service?->duration_unit }}</span>
                              </li>
                              <li>
                                  <i data-feather="user"></i>
                                  <span>{{ $cartItem?->service?->required_servicemen }} Servicemen</span>
                              </li>
                          </ul>
                          <h3>{{ Helpers::getDefaultCurrency()?->symbol }} {{ $cartItem?->service?->service_rate }} <del>{{ Helpers::getDefaultCurrency()?->symbol }} {{ $cartItem?->service?->price }}</del></h3>
                      </div>
                  </div>
              </div>
          </li>
      @empty
      
        <div class="items-no-found">
            <svg>
                <use xlink:href="{{ asset('admin/images/empty-cart.svg#empty-cart') }}"></use>
            </svg>
            {{-- <img src="{{ asset('admin/images/no-table-data.svg') }}" class="img-fluid"
                alt=""> --}}
            <p>Not Found</p>
        </div>
      @endforelse
  </ul>
</div>
