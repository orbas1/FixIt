@use('app\Helpers\Helpers')
@use('App\Enums\SymbolPositionEnum')
@extends('frontend.layout.master')

@push('css')
<!-- Flatpickr css -->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/jquery-datetimepicker/jquery.datetimepicker.min.css') }}">

<!-- Mobiscroll css -->
{{-- <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/mobiscroll/mobiscroll.css') }}"> --}}
@endpush

@section('title', $package?->title)

@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{ route('frontend.home') }}">{{__('frontend::static.bookings.home')}}</a>
    <a class="breadcrumb-item" href="{{ route('frontend.service-package.index') }}">{{ __('frontend::static.servicePackages.servicePackages')}}</a>
    <span class="breadcrumb-item active">{{ $package?->title }}</span>
</nav>
@endsection

@section('content')
@php
$services = $package->services;
@endphp

<section class="service-package-section">
    <form id="servicePackageBookingForm" action="{{route('frontend.booking.service-package.store')}}" method="POST">
        @csrf
        <div class="container-fluid-lg booking-sec">
            <input type="hidden" name="service_packages[service_package_id]" value="{{$package->id}}">
            @foreach($services as $index => $service)
            <div class="booking-sec-box">
                <div class="modal-body custom-scroll">
                    <div class="service-item">
                        <div class="service-left-box">
                            <img src="{{ $service?->media?->first()?->getUrl() }}" alt="service" class="service-img img-fluid">
                            <div class="service-title">
                                <div class="service-offer">
                                    <h4>{{ $service->title }}</h4>
                                    <div class="time">
                                        <i class="iconsax" icon-name="clock"></i>
                                        <span class="text-success">{{ $service->duration }}
                                            {{ $service->duration_unit }}</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                     @if(!empty($service?->discount) && $service?->discount > 0)
                                         @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                            <small>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->service_rate) }}</small>
                                            <span>
                                                <del>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->price) }}</del>
                                            </span> 
                                        @else
                                            <small>{{ Helpers::covertDefaultExchangeRate($service->service_rate) }} {{ Helpers::getDefaultCurrencySymbol() }}</small>
                                            <span>
                                                <del>{{ Helpers::covertDefaultExchangeRate($service->price) }} {{ Helpers::getDefaultCurrencySymbol() }}</del>
                                            </span>
                                        @endif
                                    @else
                                        @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                            <small>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->service_rate) }}</small>
                                        @else    
                                            <small>{{ Helpers::covertDefaultExchangeRate($service->service_rate) }} {{ Helpers::getDefaultCurrencySymbol() }}</small>
                                        @endif
                                    @endif
                                </div>
                                <p>{{ $service->description }}</p>
                                <div class="amount mb-xl-0">
                                    <div class="amount-detail">
                                        <ul class="amount-listing">
                                            <li>
                                                <i class="iconsax" icon-name="clock"></i>
                                                {{ __('frontend::static.services.around')}} {{ $service?->duration }} {{ $service?->duration_unit }}
                                            </li>
                                            <li>
                                                <i class="iconsax" icon-name="user-1-tag"></i>
                                                {{ __('frontend::static.services.min')}} {{ $service?->required_servicemen }} {{ __('frontend::static.services.servicemen_required_for')}}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="detail service-section w-100">
                            <label class="mb-2 fw-medium label-title">{{__('frontend::static.bookings.add_required_person')}}</label>
                            <div class="select-servicemen">
                                <p>{{__('frontend::static.bookings.home_many_person')}}</p>
                                <div class="plus-minus">
                                    <i class="iconsax sub minus-btn" data-service-id="{{ $service->id }}"
                                        icon-name="minus"></i>
                                    <input id="quantityInput-{{ $service->id }}" class="quantity-input"
                                        data-service-id="{{ $service->id }}" name="service_packages[services][{{ $index }}][required_servicemen]"
                                        type="number" value="{{ $service->required_servicemen }}"
                                        min="{{ $service->required_servicemen }}" max="100" readonly>
                                    <i class="iconsax add add-btn" data-service-id="{{ $service->id }}" icon-name="add"></i>
                                </div>
                            </div>
                            @error("service_packages.services.$index.required_servicemen")
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                            <input type="hidden" name="service_packages[services][{{ $index }}][service_id]" value="{{ $service->id }}">
                            <div class="service-section border-line" data-service-id="{{ $service->id }}">
                                <label class="mt-3 mb-2 label-title">{{__('frontend::static.bookings.choose_one_of_below')}}</label>
                                <div class="select-option">
                                    <div class="form-check">
                                        <input type="radio" id="optionone-{{$service?->id}}" name="service_packages[services][{{ $index }}][select_serviceman]" value="app_choose"
                                            class="form-radio-input service-radio" checked>
                                        <label for="optionone-{{$service?->id}}">{{__('frontend::static.bookings.let_app_choose')}}</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" id="optiontwo-{{$service?->id}}" name="service_packages[services][{{ $index }}][select_serviceman]"
                                            value="as_per_my_choice" class="form-radio-input service-radio">
                                        <label for="optiontwo-{{$service?->id}}">{{__('frontend::static.bookings.select_service_men')}}</label>
                                    </div>
                                </div>

                                <div class="as_per_my_choice" id="as_per_my_choiceDiv" style="display: none;">
                                    <button id="selectServicemenBtn" type="button"
                                        class="servicemen-lists select-servicemen-btn" data-bs-toggle="modal"
                                        data-bs-target="#checkservicemenListModal-{{$service?->id}}"
                                        data-service-id="{{ $service->id }}">
                                        + {{__('frontend::static.bookings.select_servicemen')}}
                                    </button>
                                    <div class="modal fade servicemen-list-modal"
                                        id="checkservicemenListModal-{{$service?->id}}"
                                        data-service-id="{{ $service->id }}" data-bs-backdrop="static">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h3 class="modal-title" id="checkservicemenListModalLabel">{{__('frontend::static.bookings.servicemen_list')}}</h3>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="input-group search">
                                                        <input class="form-control form-control-gray" type="text"
                                                            placeholder="{{__('frontend::static.bookings.search_here')}}">
                                                        <i class="iconsax input-icon" icon-name="search-normal-2"></i>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="servicemen-list custom-scroll">
                                                                @forelse ($servicemen as $serviceman)
                                                                <div class="servicemen-list-item">
                                                                    <div class="list">
                                                                        <input type="hidden" class="serviceman-id" value="{{ $serviceman->id }}" />

                                                                        <img src="{{ $serviceman?->media?->first()?->getUrl() }}"
                                                                            alt="feature" class="img-45">
                                                                        <div>
                                                                            <ul>
                                                                                <li>
                                                                                    <button type="button" class="detail"
                                                                                        data-bs-target="#servicemenDetailModal-{{ $serviceman->id }}"
                                                                                        data-bs-toggle="modal">{{ $serviceman?->name }}</button>
                                                                                </li>
                                                                                <li>
                                                                                    <div class="rate">
                                                                                        <img src="{{ asset('frontend/images/svg/star.svg') }}"
                                                                                            alt="star"
                                                                                            class="img-fluid star">
                                                                                        <small>{{ $serviceman?->review_ratings ?? 'Unrated' }}</small>
                                                                                    </div>
                                                                                </li>
                                                                            </ul>
                                                                            <div class="experience">
                                                                                @if($serviceman?->experience_duration)
                                                                                <p>{{ $serviceman?->experience_duration}}
                                                                                    {{ $serviceman?->experience_interval }} {{__('frontend::static.bookings.of_experience')}}
                                                                                </p>
                                                                                @else
                                                                                <p>
                                                                                    {{__('frontend::static.bookings.fresher')}}
                                                                                </p>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input type="checkbox" class="form-check-input serviceman-checkbox" data-service-id="{{ $service->id }}" data-serviceman-id="{{ $serviceman->id }}">
                                                                    </div>
                                                                </div>
                                                                @empty
                                                                <div class="no-data-found">
                                                                    <p>{{__('frontend::static.bookings.servicemen_not_found')}}</p>
                                                                </div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-solid confirm-selection" data-service-id="{{ $service->id }}">{{__('frontend::static.bookings.save')}}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="serviceman selected-servicemen-list" data-service-id="{{ $service->id }}"
                                        style="display: none;">

                                    </div>
                                    <input type="hidden" name="service_packages[services][{{ $index }}][serviceman_id]" id="selectedServicemen-{{ $service->id }}" value="">
                                </div>
                            </div>
                            <div>
                                <li class="d-flex align-items-start booking-list mt-3">
                                    <div class="booking-data w-100">
                                        @includeIf('frontend.booking.select-address', ['name' => "service_packages[services][$index][address_id]"])
                                    </div>
                                    @error("service_packages.services.$index.address_id")
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </li>
                            </div>
                            <div class="booking-data mt-3">
                                <h3 class="mb-2">Date and Time</h3>
                                <div class="date-time-picket-sec">
                                    <div class="select-option">
                                        <div class="form-check mb-0">
                                            <input type="radio" id="customDateTime" value="custom" name="service_packages[services][{{$index}}][select_date_time]"
                                                class="form-radio-input" checked>
                                            <label for="customDateTime">{{__('frontend::static.bookings.custom_date_time')}}</label>
                                        </div>
                                        <div class="d-flex align-items-center gap-sm-3 gap-2">
                                            <div class="form-check mb-0">
                                                <input type="radio" id="timeSlotDateTime" value="timeslot"
                                                    name="service_packages[services][{{$index}}][select_date_time]" class="form-radio-input" data-bs-toggle="modal"
                                                    data-bs-target="#datetimeModal-{{$service?->id}}">
                                            </div>
                                            <label for="timeSlotDateTime">{{__('frontend::static.bookings.as_per_provider_time_slot')}}</label>
                                        </div>
                                        <div class="modal fade date-time-modal" id="datetimeModal-{{$service?->id}}">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h3 class="modal-title mb-0">{{__('frontend::static.bookings.select_provider_date_time_slot')}}</h3>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body pb-0">
                                                        <div class="row g-3">
                                                            <div class="date-time-slot-box">
                                                                <input id="datetimepicker" type="date" class="form-control flatpicker-calender" placeholder="Select Date" />
                                                            </div>
                                                            <div class="col-12">
                                                                <div id="timeSlotsContainer" class="time-slot-main-box"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer d-block">
                                                        <div class="inline-picker-btn m-0">
                                                            <button type="button" class="btn btn-solid providerDateTimeBtn" id="providerDateTimeBtn"
                                                                data-service-id="{{$service->id}}" data-index="{{$index}}">
                                                                {{__('frontend::static.bookings.select_date_time')}}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="date-time-picker">
                                        <div class="input-group flatpicker-calender">
                                            <input class="form-control form-control-gray date-picker" id="date-picker-{{ $index }}"
                                                type="date" placeholder="Select Date">
                                            <i class="iconsax input-icon" icon-name="calendar-1"></i>
                                        </div>

                                        <div class="input-group">
                                            <input class="form-control form-control-gray time-picker" id="time-picker-{{ $index }}"
                                                type="time" placeholder="Select time">
                                            <i class="iconsax input-icon" icon-name="clock"></i>
                                        </div>
                                        <input type="hidden" name="service_packages[services][{{ $index }}][date_time]" id="dateTime-{{ $index }}" value="">
                                    </div>
                                </div>
                            </div>
                            @error("service_packages.services.$index.select_date_time")
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                            @error("service_packages.services.$index.date_time")
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="wizard-footer">
            <button type="button" class="btn btn-outline" id="cancelBtn" onclick="window.history.back();">{{__('frontend::static.bookings.cancel_btn')}}</button>
            <button type="submit" class="btn btn-solid spinner-btn" id="confirmBookingBtn">{{__('frontend::static.bookings.confirm_booking')}}
                <span class="spinner-border spinner-border-sm" style="display: none;"></span>
            </button>
        </div>
    </form>
</section>

@includeIf('frontend.address.add')

@includeIf('frontend.inc.modal', ['multiple' => true])

@endsection

@push('js')
<!-- Flat-picker js -->
<script src="{{ asset('frontend/js/flat-pickr/flatpickr.js') }}"></script>
<script src="{{ asset('frontend/js/flat-pickr/custom-flatpickr.js') }}"></script>

<!-- Mobiscroll js -->
{{-- <script src="{{ asset('frontend/js/mobiscroll/mobiscroll.js') }}"></script>
<script src="{{ asset('frontend/js/mobiscroll/custom-mobiscroll.js') }}"></script> --}}

<script>
    $(document).ready(function() {
        $("#servicePackageBookingForm").validate({

            ignore: [],
            rules: {

            },
            messages: {
                'service_packages[services][*][required_servicemen]': {
                    required: "Please specify the required number of servicemen.",
                    min: "At least one serviceman is required."
                },
                'service_packages[services][*][address_id]': {
                    required: "Please select an address."
                },
                'service_packages[services][*][date_time]': {
                    required: "Please select a date and time."
                },
                'service_packages[services][*][select_serviceman]': {
                    required: "Please choose how the servicemen will be selected."
                }
            },
            errorPlacement: function(error, element) {
                if (element.closest('.plus-minus').length) {
                    error.insertAfter(element.closest('.plus-minus'));
                } else if (element.closest('.select-servicemen').length) {
                    error.insertAfter(element.closest('.select-servicemen'));
                } else if (element.closest('.date-time-picker').length) {
                    error.insertAfter(element.closest('.date-time-picker'));
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function(form) {
                form.submit();
            },
            invalidHandler: function(event, validator) {
                $("#confirmBookingBtn .spinner-border").hide();
                $("#confirmBookingBtn").prop("disabled", false);
            }
        });

        $("input[name^='service_packages[services]'][name$='[required_servicemen]']").each(function() {
            $(this).rules("add", {
                required: true,
                min: 1
            });
        });

        // For address_id select fields
        $("select[name^='service_packages[services]'][name$='[address_id]']").each(function() {
            $(this).rules("add", {
                required: true
            });
        });

        // For date_time input fields
        $("input[name^='service_packages[services]'][name$='[date_time]']").each(function() {
            $(this).rules("add", {
                required: true
            });
        });

        // For select_serviceman radio buttons (group of radio buttons)
        $("input[type=radio][name^='service_packages[services]'][name$='[select_serviceman]']").each(function() {
            $(this).rules("add", {
                required: true
            });
        });

        // Validate quantity input dynamically (e.g., after changing value)
        $(".quantity-input").on("input", function() {
            $(this).valid(); // Trigger validation on input change
        });

        // Ensure radio buttons for "select_serviceman" are validated when changed
        $("input[type=radio][name^='service_packages[services]'][name$='[select_serviceman]']").on("change", function() {
            $(this).valid(); // Trigger validation on radio button change
        });

    });

    const maxBookingDays = {{ Helpers::getsettings()['default_creation_limits']['max_booking_days'] ?? 30 }};
    const today = new Date();
    const maxDate = new Date();
    maxDate.setDate(today.getDate() + maxBookingDays);

    flatpickr("#datetimepicker", {
        dateFormat: "d-m-Y",
        minDate: "today",
        maxDate: maxDate,
    });

    $(".date-picker").each(function() {
        flatpickr(this, {
            dateFormat: "Y-m-d",
            minDate: new Date(),
            maxDate: maxDate,
        });
    });

    $(".time-picker").each(function() {
        flatpickr(this, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            minTime: new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            })
        });
    });

    const providerTimeSlot = @json($providerTimeSlot);

    $(document).on('change', '#datetimepicker', function () {
        const modalId = $(this).closest('.modal').attr('id'); // Get modal ID
        const selectedDate = new Date(this.value);
        const dayName = selectedDate.toLocaleDateString('en-US', { weekday: 'long' }).toUpperCase();
        const daySlot = providerTimeSlot.time_slots.find(slot => slot.day === dayName && slot.is_active === 1);
        const container = $(`#${modalId} #timeSlotsContainer`);
        container.empty();

        if (daySlot && daySlot.slots.length > 0) {
            daySlot.slots.forEach(time => {
                const btn = $('<button></button>', {
                    class: 'btn btn-outline-primary',
                    text: time,
                    type: 'button',
                    'data-time': time
                });
                container.append(btn);
            });
        } else {
            container.html('<p class="no-data">No slots available for this day.</p>');
        }
    });

    $(document).on('click', '#timeSlotsContainer button', function () {
        const modalId = $(this).closest('.modal').attr('id'); // Get modal ID
        $(`#${modalId} #timeSlotsContainer button`).removeClass('active');
        $(this).addClass('active');
        selectedSlotTime = $(this).data('time');
    });

    $(document).on('click', '.providerDateTimeBtn', function () {
        const modalId = $(this).closest('.modal').attr('id'); // Get modal ID
        const selectedDateStr = $(`#${modalId} #datetimepicker`).val();
        const [day, month, year] = selectedDateStr.split('-');
        const selectedDateObj = new Date(`${year}-${month}-${day}`);

        if (!selectedDateObj || !selectedSlotTime) {
            alert("Please select a date and time slot.");
            return;
        }

        const formattedDate = selectedDateObj.toISOString().split('T')[0];
        const formattedTime = selectedSlotTime;

        // Use the 'index' from data attributes of the button to select the right input fields
        const index = $(this).data('index');

        // Set values to fields based on the dynamic index
        $(`#date-picker-${index}`).val(formattedDate);  // Set date picker value
        $(`#time-picker-${index}`).val(formattedTime);  // Set time picker value
        $(`#dateTime-${index}`).val(`${formattedDate} ${formattedTime}`);  // Set hidden dateTime input value

        // Hide the modal after selection
        $(`#${modalId}`).modal('hide');
    });

    $(document).on('change', '.service-radio', function() {
        $(this).closest('.service-section')
            .find('.as_per_my_choice')
            .toggle(this.value === 'as_per_my_choice');
    }).find('.service-radio:checked').trigger('change'); // Trigger on load

    $('input[type="checkbox"]').on('change', function() {
        const serviceId = $(this).data('service-id');
        const maxServicemen = $(`.servicemen-list-modal[data-service-id="${serviceId}"]`).data(
            'max-servicemen');
        const checkedCount = $(`input[name="servicemen-list-${serviceId}"]:checked`).length;
        if (checkedCount > maxServicemen) {
            $(this).prop('checked', false);
            alert(`You can only select up to ${maxServicemen} servicemen for this service.`);
        }
    });


    // Toggle checkbox when clicking the servicemen list item, but exclude the detail button
    $(document).on('click', '.servicemen-list-item', function(e) {
        if (!$(e.target).hasClass('detail')) {
            const checkbox = $(this).find('.serviceman-checkbox');
            checkbox.prop('checked', !checkbox.prop('checked'));
        }
    });

    // Show serviceman details in modal when clicking the detail button
    $(document).on('click', '.detail', function(e) {
        e.stopPropagation();
        const targetModal = $(this).data('bs-target');
        $(targetModal).modal('show');
    });


    // Save button click for each modal
    $(document).on('click', '.confirm-selection', function() {
        const serviceId = $(this).data('service-id');
        const selectedServicemenContainer = $(
            `.selected-servicemen-list[data-service-id="${serviceId}"]`);
        let selectedServicemenHtml = '';
        let servicemenIds = [];
        $(`#checkservicemenListModal-${serviceId} .serviceman-checkbox:checked`).each(function() {
            const servicemanItem = $(this).closest('.servicemen-list-item');
            const servicemanId = servicemanItem.find('.serviceman-id').val();
            servicemenIds.push(servicemanId);
            selectedServicemenHtml += `
                    <div class="servicemen-list-item">
                        <div class="list">
                            <img src="${servicemanItem.find('img').attr('src')}" alt="feature" class="img-45">
                            <div>
                                <ul>
                                    <li><h5>${servicemanItem.find('.detail').text()}</h5></li>
                                    <li>
                                        <div class="rate">
                                            <small>${servicemanItem.find('.rate small').text()}</small>
                                        </div>
                                    </li>
                                </ul>
                                <p>${servicemanItem.find('p').text()}</p>
                            </div>
                        </div>
                    </div>`;
        });

        if (selectedServicemenHtml) {
            selectedServicemenContainer.html(selectedServicemenHtml).show();
        } else {
            selectedServicemenContainer.hide();
        }
        $(`#serviceman_id_${serviceId}`).val(servicemenIds.join(','));
        $(`#checkservicemenListModal-${serviceId}`).modal('hide');
    });

    $('.plus-minus').on('click', '.add-btn, .minus-btn', function() {
        const serviceId = $(this).data('service-id');
        const quantityInput = $(`#quantityInput-${serviceId}`);
        let newValue = parseInt(quantityInput.val(), 10);
        quantityInput.val(newValue);
        $(`.servicemen-list-modal[data-service-id="${serviceId}"]`).data('max-servicemen', quantityInput.val());
    });

    $(document).on('change', '.serviceman-checkbox', function() {
        const serviceId = $(this).data('service-id');
        const maxServicemen = +$(`.servicemen-list-modal[data-service-id="${serviceId}"]`).data(
            'max-servicemen');
        const checkedCount = $(`input[name="servicemen-list-${serviceId}"]:checked`).length;
        if (checkedCount > maxServicemen) {
            $(this).prop('checked', false);
            alert(`You can only select up to ${maxServicemen} servicemen.`);
        }
    });

    $('.confirm-selection').click(function() {
        let serviceId = $(this).data('service-id');
        let selectedIds = $(`.serviceman-checkbox[data-service-id="${serviceId}"]:checked`)
            .map(function() {
                return $(this).data('serviceman-id');
            }).get().join(', ');

        $(`#selectedServicemen-${serviceId}`).val(selectedIds);
        $(`#checkservicemenListModal-${serviceId}`).modal('hide');
    });

    $('[class*="date-picker"], [class*="time-picker"]').on('change', function() {
        let index = $(this).closest('.date-time-picker').find('input[type="hidden"]').attr('id').split('-').pop();
        let date = $(this).closest('.date-time-picker').find('.date-picker').val();
        let time = $(this).closest('.date-time-picker').find('.time-picker').val();

        if (date && time) {
            $(`#dateTime-${index}`).val(`${date} ${time}`);
        }
    });

    // $('.providerDateTimeBtn').on('click', function() {
    //     const serviceId = $(this).data('service-id');
    //     const index = $(this).data('index');
    //     const selectedDateTime = mobiscroll.getInst(document.getElementById('time-slot-' + serviceId)).getVal();
    //     const selectedDate = selectedDateTime.toLocaleDateString('en-GB'); // format: dd-mm-yyyy
    //     const selectedTime = selectedDateTime.toLocaleTimeString([], {
    //         hour: '2-digit',
    //         minute: '2-digit'
    //     }); // format: HH:MM
    //     const formattedDateTime = `${selectedDateTime.toLocaleDateString('en-GB')}, ${selectedDateTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
    //     $('#dateTime-' + index).val(formattedDateTime);
    //     $('#date-picker-' + index)[0]._flatpickr.setDate(selectedDate, true); // Date only
    //     $('#time-picker-' + index)[0]._flatpickr.setDate(selectedTime, true); // Time only
    //     $('#datetimeModal-' + serviceId).modal('hide');
    // });
</script>

@endpush