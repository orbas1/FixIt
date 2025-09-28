
@if($user && $user?->addresses?->isNotEmpty())
<ul class="delivery-location-list">
    @forelse($user?->addresses as $address)
            <li class="card delivery-location">
                <div class="location-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="location-icon">
                            <img src="{{ asset('frontend/images/svg/home-1.svg') }}" alt="home">
                        </div>
                        <div class="name">
                            <h4>{{ $user?->name }}</h4>
                            <span>({{ $user?->code }}){{ $user?->phone }}</span>
                        </div>
                    </div>
                    <span class="badge">{{ $address?->type }}</span>
                </div>
                <div class="address">
                    <label>Address:</label>
                    <p>{{ $address?->address }}</p>
                </div>
                <div class="address-bottom-box">
                    <div class="action">
                        <input class="radio address-select" type="radio" value="{{ $address?->id }}" name="address_id">
                        <button type="button" class="btn select-btn btn-outline">Select this</button>
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
            <p>No Items Found</p>
    </div>

    @endforelse
</ul>
@else
<div class="form-group">
    <div class="items-no-found">
        <svg>
            <use xlink:href="{{ asset('admin/images/empty-cart.svg#empty-cart') }}"></use>
        </svg>
        {{-- <img src="{{ asset('admin/images/no-table-data.svg') }}" class="img-fluid"
            alt=""> --}}
            <p>No Items Found</p>
    </div>
</div>
@endif
