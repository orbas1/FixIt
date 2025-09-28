@use('app\Helpers\Helpers')
@use('App\Enums\SymbolPositionEnum')

@auth
    @isset($service)
    <!-- Book Service Modal -->
    <div class="modal fade book-service" id="bookServiceModal-{{ $service->id }}" tabindex="-1" aria-labelledby="bookServiceModalLabel-{{ $service->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('frontend.cart.add') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title" id="bookServiceModalLabel-{{$service->id}}">{{ __('frontend::static.modal.book_your_service') }}</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Hidden input to store the service ID -->
                        <input type="hidden" name="service_id" value="{{ $service->id }}">

                        <div class="service">
                            <img src="{{ $service->web_img_thumb_url }}" alt="service">
                            <div class="book-service-title">
                                <h3>{{ $service->title }}</h3>
                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                    <span>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->service_rate) }}</span>
                                @else
                                    <span>{{ Helpers::covertDefaultExchangeRate($service->service_rate) }} {{ Helpers::getDefaultCurrencySymbol() }}</span>
                                @endif                            
                            </div>
                        </div>

                        <!-- Additional Services Section -->
                        @if($service->additionalServices->count() > 0)
                            <div>
                                <h4 class="service-title">{{ __('frontend::static.modal.select_additional_services') }}</h4>
                                <div class="select-additional qty">
                                    @foreach($service->additionalServices as $index => $additionalService)
                                        <div class="form-check">
                                            <input type="checkbox" id="additional-{{ $additionalService->id }}" name="additional_services[{{ $index }}][id]" value="{{ $additionalService->id }}" class="form-check-input">
                                            <label for="additional-{{ $additionalService->id }}">
                                                {{ $additionalService->title }} - 
                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                    <span class="additional-price"  data-base-price="{{ Helpers::covertDefaultExchangeRate($additionalService->price) }}">{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($additionalService->price) }}</span>
                                                @else
                                                    <span class="additional-price"  data-base-price="{{ Helpers::covertDefaultExchangeRate($additionalService->price) }}">{{ Helpers::covertDefaultExchangeRate($additionalService->price) }} {{ Helpers::getDefaultCurrencySymbol() }}</span>
                                                @endif
                                            </label>
                                            <div class="plus-minus">
                                                <i class="iconsax sub qtyminus" icon-name="minus"></i>
                                                    <input class="additional_services_qty" id="" name="additional_services[{{ $index }}][qty]" type="number" value="1" min="1" max="100" readonly>
                                                <i class="iconsax add qtyadd" icon-name="add"></i>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <!-- Input for required servicemen -->
                        <div>
                            <h4 class="service-title">{{ __('frontend::static.modal.add_required_person') }}</h4>
                            <div class="select-servicemen">
                                <p>{{ __('frontend::static.modal.home_many_person') }}</p>
                                <div class="plus-minus">
                                    <i class="iconsax sub" icon-name="minus" id="minus"></i>
                                        <input id="quantityInput" name="required_servicemen" type="number" value="{{ $service->required_servicemen }}" min="{{ $service->required_servicemen }}" max="100" readonly>
                                    <i class="iconsax add" icon-name="add" id="add"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Radio buttons for service option selection -->
                        <div>
                            <h4 class="service-title">{{ __('frontend::static.modal.choose_one_of_below') }}</h4>
                            <div class="select-option">
                                <div class="form-check">
                                    <input type="radio" id="optionone-{{ $service->id }}" name="select_serviceman" value="app_choose" class="form-radio-input" checked>
                                    <label for="optionone-{{ $service->id }}">{{ __('frontend::static.modal.let_app_choose') }}</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" id="optiontwo-{{ $service->id }}" name="select_serviceman" value="as_per_my_choice" class="form-radio-input">
                                    <label for="optiontwo-{{ $service->id }}">{{ __('frontend::static.modal.select_service_men') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Submit button for form submission -->
                    <div class="modal-footer pt-0">
                        <button type="submit" class="btn btn-solid spinner-btn">{{ __('frontend::static.modal.book_now') }} <span class="spinner-border spinner-border-sm" style="display: none;"></span></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Book Service Modal -->
    @endisset
@endauth

@isset($servicemen)
<!-- Servicemen list modal for multiple servicemen-->
<div class="modal fade servicemen-list-modal" id="checkservicemenListModal" tabindex="-1"
    aria-labelledby="checkservicemenListModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="checkservicemenListModalLabel">{{ __('frontend::static.modal.servicemen_list') }}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="input-group search">
                    <input class="form-control form-control-gray" type="text" placeholder="Search here...">
                    <i class="iconsax input-icon" icon-name="search-normal-2"></i>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="servicemen-list custom-scroll">
                            @forelse ($servicemen as $serviceman)
                            <div class="servicemen-list-item">
                                <div class="list">
                                    <input type="hidden" class="serivcemanId" data-id="{{ $serviceman?->id }}" />
                                    <img src="{{ Helpers::isFileExistsFromURL($serviceman?->media?->first()?->getUrl(), true) }}" alt="feature"
                                        class="img-45">
                                    <div>
                                        <ul>
                                            <li>
                                                <button class="detail"
                                                    data-bs-target="#servicemenDetailModal-{{ $serviceman->id }}"
                                                    data-bs-toggle="modal">{{ $serviceman?->name }}</button>
                                            </li>
                                            <li>
                                                <div class="rate">
                                                    <img src="{{ asset('frontend/images/svg/star.svg') }}" alt="star"
                                                        class="img-fluid star">
                                                    <small>{{ $serviceman?->review_ratings ??  __('frontend::static.modal.unrated') }}</small>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="experience">
                                            @if($serviceman?->experience_duration)
                                            <p>{{ $serviceman?->experience_duration}}
                                                {{ $serviceman?->experience_interval }} {{ __('frontend::static.modal.of_experience') }}
                                            </p>
                                            @else
                                            <p>
                                                {{ __('frontend::static.modal.fresher') }}
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="servicemen1" name="servicemen-list"
                                        class="form-check-input">
                                </div>
                            </div>
                            @empty
                            <div class="no-data-found">
                                <p>{{ __('frontend::static.modal.servicemen_not_found') }}</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" class="btn btn-solid" id="confirmSelection">{{ __('frontend::static.modal.save') }}</a>
            </div>
        </div>
    </div>
</div>
<!-- Servicemen list modal for multiple servicemen-->

<!-- Servicemen detail modal-->
@foreach ($servicemen as $serviceman)
<div class="modal fade servicemen-detail-modal" id="servicemenDetailModal-{{ $serviceman?->id }}"
    data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel-{{ $serviceman?->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
            <a href="#">    
                <i class="iconsax" icon-name="chevron-left"></i>
            </a>    
            <h3 class="modal-title" id="checkservicemenDetailModalLabel">{{ __('frontend::static.modal.serviceman_details') }}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="provider-card">
                    <div class="provider-detail">
                        <div class="provider-content">
                            <div class="profile-bg"></div>
                            <div class="profile">
                                <img src="{{ Helpers::isFileExistsFromURL($serviceman?->media?->first()?->getUrl(), true) }}" alt="girl" class="img">
                                <div class="d-flex align-content-center gap-2 mt-2">
                                    <h3>{{ $serviceman?->name }}</h3>
                                    <div class="rate">
                                        <img src="{{ asset('frontend/images/svg/star.svg') }}" alt="star"
                                            class="img-fluid star">
                                        <small>{{ $serviceman?->review_ratings ?? 0.0 }}</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <p class="text-light">
                                        {{ $serviceman?->experience_duration }}
                                        {{ $serviceman?->experience_interval }} {{ __('frontend::static.modal.of_experience') }}
                                    </p>
                                    <div class="location">
                                        <i class="iconsax" icon-name="location"></i>
                                        <h5>{{ $serviceman?->primary_address?->state?->name }} -
                                            {{ $serviceman?->primary_address?->country?->name }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="view br-6 mt-3">
                                <div class="d-flex align-items-center justify-content-between gap-1">
                                    <span>{{ __('frontend::static.modal.services_delivered') }}</span>
                                    <small class="value"> {{ $serviceman?->served }} {{ __('frontend::static.modal.served') }}</small>
                                </div>
                            </div>
                            <div class="information">
                                @if ($serviceman->knownLanguages?->toArray())
                                <div>
                                    <p class="mt-3 mb-2">Known languages</p>
                                    @php
                                    $knownLanguages = $serviceman->knownLanguages;
                                    @endphp
                                    <div class="d-flex align-content-center gap-3 mt-2">
                                        @foreach ($knownLanguages as $language)
                                        <button class="btn btn-solid-gray">{{ $language?->key }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
@endisset

@isset($data)

@endisset

@push('js')
@isset($servicemen)
<script>
    (function($) {
        $(document).ready(function() {

            $('.search input').on('input', function() {
                var searchTerm = $(this).val().toLowerCase();
                $('.servicemen-list-item').each(function() {
                    var servicemanName = $(this).find('button.detail').text().toLowerCase();
                    if (servicemanName.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });


            $(document).on('click', '.servicemen-list-item', function(e) {
                if (!$(e.target).closest('.form-check-input').length && !$(e.target).closest('.detail').length) {
                    const checkbox = $(this).find('input[type="checkbox"]');
                    checkbox.prop('checked', !checkbox.prop('checked'));
                }
            });

            $(document).on('click', '.form-check-input', function(e) {
                e.stopPropagation();
            });

            $(document).on('click', '.detail', function(e) {
                e.stopPropagation();
                const targetModal = $(this).data('bs-target');
                $(targetModal).modal('show');
            });

            $('#confirmSelection').on('click', function() {
                var selectedServicemen = '';
                var servicemenIds = [];
                $('.servicemen-list-item input:checked').each(function() {
                    let item = $(this).closest('.servicemen-list-item');
                    let servicemenId = item.find('.serivcemanId').attr('data-id');
                    servicemenIds.push(servicemenId);
                    selectedServicemen += `
                <div class="servicemen-list-item">
                    <div class="list">
                        <img src="${item.find('img').attr('src')}" alt="feature" class="img-45">
                        <div>
                            <p>Servicemen</p>
                            <ul>
                                <li><h5>${item.find('.detail').text()}</h5></li>
                                <li>
                                    <div class="rate">
                                        <img src="{{ asset('frontend/images/svg/star.svg') }}" alt="star" class="img-fluid star">
                                        <small>${item.find('.rate small').text()}</small>
                                    </div>
                                </li>
                            </ul>
                             <p>${item.find('.experience p').text()}</p>
                        </div>
                    </div>
                </div>`;
                });

                if (selectedServicemen != '') {
                    $('.selected-men').html(selectedServicemen);
                    $('.selectServicemenDiv').hide();
                    $('.selectedServicemenDiv').show();
                    $('#serviceman_id').val(servicemenIds.toString())
                } else {
                    $('.selectedServicemenDiv').hide();
                    $('.selectServicemenDiv').show();
                }

                $('#checkservicemenListModal').modal('hide');

            });
        });
    })(jQuery);
</script>
@endisset
@endpush