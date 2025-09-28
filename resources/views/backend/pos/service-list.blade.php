<div class="tab-content" id="categoryTabsContent">
  @forelse($categories as $index => $category)
    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="category-{{ $index }}" role="tabpanel">
    <div class="col-12">
      <div class="contentbox bg-transparent">
      <div class="inside p-0">
        <div class="contentbox-title">
        <div class="contentbox-subtitle">
          <h3>Service List - {{ $category?->title }}</h3>
        </div>
        </div>

        <div class="row g-sm-4 g-3">
        @forelse($category->services?->whereNull('parent_id') as $service)
     
        <div class="col-xxl-4 col-xl-6 col-md-4 col-sm-6">
          <div class="servicemen-box">
          <div class="servicemen-image">
          <img src="{{ $service?->web_img_thumb_url }}" class="img-fluid" alt="{{ $service?->title }}">
          </div>
          <div class="servicemen-details">
          <h4>{{ $service?->title }}</h4>
          <ul class="service-list">
          <li class="time">
            <i data-feather="clock"></i>
            <span>{{ $service?->duration }} {{ $service?->duration_unit }}</span>
          </li>
          <li>
            <i data-feather="user"></i>
            <span>{{ $service?->required_servicemen }} Servicemen</span>
          </li>
          </ul>
          <h3>${{ number_format($service?->service_rate, 2) }}
          <del>{{ number_format($service?->price, 2) }}</del></h3>
          </div>
          <div class="service-bottom-box">
          <div class="footer-detail">
          @php
        $media = $service?->user?->getFirstMedia('image');
        $imageUrl = $media ? $media->getUrl() : null;
      @endphp
          @if ($imageUrl)
        <a href="#!">
        <img src="{{ $imageUrl }}" alt="feature" class="img-fluid">
        </a>
      @else
      <div class="initial-letter">
      {{ strtoupper(substr($service?->user?->name, 0, 1)) }}
      </div>
    @endif
          <div>
            <p>{{ $service?->user?->name }}</p>
            <div class="rate">
            <img src="http://127.0.0.1:8000/frontend/images/svg/star.svg" alt="star" class="img-fluid star">
            <small>5</small>
            </div>
          </div>
          </div>

          <button type="button" class="btn btn-primary" data-bs-toggle="modal"
          data-bs-target="#bookNowModal-{{ $service?->id }}">Book
          Now</button>
          </div>
          </div>
        </div>
        @empty
    @endforelse
        </div>

      </div>
      </div>
    </div>
    </div>
    @empty
  @endforelse
</div>
