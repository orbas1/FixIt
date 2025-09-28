@extends('backend.layouts.master')
@section('title', __('static.report.booking_reports'))
@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/flatpickr.min.css') }}">
@endpush
@use('App\Enums\PaymentStatus')
@use('App\Helpers\Helpers')
@php
    $providers = Helpers::getAllProviders();
    $users = Helpers::getAllUsers();
    $bookingStatus = Helpers::getBookingStatus();
    $paymentStatus = PaymentStatus::ALL;
    $services = Helpers::getAllServices();
    $paymentMethodColorClasses = Helpers::getPaymentStatusColorClasses();
@endphp
@section('content')
    <div class="category-main">
        <form id="filterForm" method="POST" action="{{ route('backend.booking-report.export') }}"
            enctype="multipart/form-data">
            @method('POST')
            @csrf
            <div class="row">
                <div class="col-xl-3">
                    <div class="p-sticky">
                        <div class="contentbox">
                            <div class="inside">
                                <div class="contentbox-title">
                                    <h3>{{ __('static.report.filter') }}</h3>
                                    <button type="button" class="btn clear-btn" style="display: none;">Clear all</button>
                                </div>
                                <div class="rider-height custom-scrollbar">
                                    <div class="form-group">
                                        <label for="provider">{{ __('static.report.provider') }}</label>
                                        <select class="select-2 form-control filter-dropdown disable-all" id="provider"
                                            name="provider[]" multiple
                                            data-placeholder="{{ __('static.report.select_provider') }}">
                                            <option value="all">{{ __('static.report.all') }}</option>
                                            @foreach ($providers as $provider)
                                                @php
                                                    $media = $provider->getFirstMedia('image');
                                                    $imageUrl = $media
                                                        ? $media->getUrl()
                                                        : strtoupper(substr($provider?->name, 0, 1));
                                                @endphp
                                                <option value="{{ $provider->id }}" sub-title="{{ $provider->email }}"
                                                    image="{{ $imageUrl }}">
                                                    {{ $provider->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="user">{{ __('static.report.user') }}</label>
                                        <select class="select-2 form-control filter-dropdown disable-all" id="user"
                                            name="user[]" multiple
                                            data-placeholder="{{ __('static.report.select_user') }}">
                                            <option value="all">{{ __('static.report.all') }}</option>
                                            @foreach ($users as $user)
                                                @php
                                                    $media = $user->getFirstMedia('image');
                                                    $imageUrl = $media
                                                        ? $media->getUrl()
                                                        : strtoupper(substr($user?->name, 0, 1));
                                                @endphp
                                                <option value="{{ $user->id }}" sub-title="{{ $user->email }}"
                                                    image="{{ $imageUrl }}">
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="ride_status">{{ __('static.report.booking_status') }}</label>
                                        <select class="select-2 form-control filter-dropdown disable-all"
                                            id="booking_status" name="booking_status[]" multiple
                                            data-placeholder="{{ __('static.report.select_booking_status') }}">
                                            <option value="all">{{ __('static.report.all') }}</option>
                                            @foreach ($bookingStatus as $status)
                                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="payment_status">{{ __('static.report.payment_status') }}</label>
                                        <select class="select-2 form-control filter-dropdown disable-all"
                                            id="payment_status" name="payment_status[]" multiple
                                            data-placeholder="{{ __('static.report.select_payment_status') }}">
                                            <option value="all">{{ __('static.report.all') }}</option>
                                            @foreach ($paymentStatus as $status)
                                                <option value="{{ $status }}">{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="start_end_date">{{ __('static.report.select_date') }}</label>
                                        <input type="text" class="form-control filter-dropdown" id="date-range"
                                            name="start_end_date"
                                            placeholder="{{ __('static.service_package.select_date') }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="service">{{ __('static.report.service') }}</label>
                                        <select class="select-2 form-control filter-dropdown disable-all" id="service"
                                            name="service[]" multiple
                                            data-placeholder="{{ __('static.report.select_service') }}">
                                            <option value="all">{{ __('static.report.all') }}</option>
                                            @foreach ($services as $service)
                                                @php
                                                    $locale = app()->getLocale();
                                                    $existingImages = $service
                                                        ->getMedia('thumbnail')
                                                        ->filter(function ($media) use ($locale) {
                                                            return $media->getCustomProperty('language') === $locale;
                                                        })
                                                        ->first();
                                                @endphp
                                                <option value="{{ $service->id }}"
                                                    image="{{ $existingImages ? $existingImages->getUrl() : '' }}">
                                                    {{ $service->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="contentbox">
                        <div class="inside">
                            <div class="contentbox-title">
                                <h3>{{ __('static.report.booking_reports') }}</h3>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#reportExportModal">
                                    {{ __('static.report.export') }}
                                    <i class="ri-upload-line"></i>
                                </button>
                            </div>
                            <div class="col">
                                <div class="booking-report-table table-main template-table m-0 loader-table">
                                    <div class="table-responsive custom-scrollbar m-0">
                                        <table class="table" id="bookingTable">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('static.report.booking_number') }}</th>
                                                    <th>{{ __('static.report.provider') }}</th>
                                                    <th>{{ __('static.report.user') }}</th>
                                                    <th>{{ __('static.report.booking_status') }}</th>
                                                    <th>{{ __('static.report.payment_method') }}</th>
                                                    <th>{{ __('static.report.payment_status') }}</th>
                                                    <th>{{ __('static.report.service') }}</th>
                                                    <th>{{ __('static.report.amount') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <div class="report-loader-wrapper" style="display:none;">
                                                    <div class="loader"></div>
                                                </div>
                                            </tbody>
                                        </table>
                                        <nav>
                                            <ul class="pagination justify-content-center mt-0 mb-3" id="report-pagination">
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade export-modal confirmation-modal" id="reportExportModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exportModalLabel">{{ __('static.modal.export_data') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                        <div class="modal-body export-data">
                            <div class="main-img">
                                <img src="{{ asset('admin/images/svg/export.svg') }}" />
                            </div>
                            <div class="form-group">
                                <label for="exportFormat">{{ __('static.modal.select_export_format') }}</label>
                                <select id="exportFormat" name="format" class="form-select">
                                    <option value="csv">{{ __('static.modal.csv') }}</option>
                                    <option value="excel">{{ __('static.modal.excel') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">{{ __('static.modal.close') }}</button>
                            <button type="submit" class="btn btn-primary spinner-btn" id="submitBtn">{{ __('static.modal.export') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('js')
    <script src="{{ asset('admin/js/flatpickr.js') }}"></script>
    <script>
        $(document).ready(function() {

            function checkFilters() {
                let hasValue = false;
                $('.filter-dropdown').each(function() {
                    if ($(this).val() && $(this).val().length > 0) {
                        hasValue = true;
                    }
                });
                $('.clear-btn').toggle(hasValue);
            }

            $('.filter-dropdown').change(function() {
                checkFilters();
            });

            $('.clear-btn').click(function() {
                location.reload();
            });

            checkFilters();

            fetchBookingReportTable(page = 1);

            $('.filter-dropdown').change(function() {
                fetchBookingReportTable();
            })

            $('#filterForm').on('submit', function() {
                setTimeout(function() {
                    $('.spinner-btn').prop('disabled', false);
                    $('.spinner-btn .spinner').remove();

                    var modal = bootstrap.Modal.getInstance($('#reportExportModal')[0]);
                    modal.hide();

                }, 3000);
            });

            function fetchBookingReportTable(page = 1) {
                $('.report-loader-wrapper').show()
                let formData = $('#filterForm').serialize();
                formData += '&page=' + page;
                var $csrfToken = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    url: '{{ route('backend.booking-report.filter') }}',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $csrfToken
                    },
                    success: function(response) {
                        $('#bookingTable tbody').html(response.bookingReportTable);
                        $('.pagination').html(response.pagination);
                    },
                    complete: function() {
                        $('.report-loader-wrapper').hide();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            }

            $(document).on('click', '#report-pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const page = new URLSearchParams(url.split('?')[1]).get('page');

                fetchBookingReportTable(page);
            });

            $('.disable-all').on('change', function() {
                const $currentSelect = $(this);
                const selectedValues = $currentSelect.val();
                const allOption = "all";

                if (selectedValues && selectedValues.includes(allOption)) {

                    $currentSelect.val([allOption]);
                    $currentSelect.find('option').not(`[value="${allOption}"]`).prop('disabled', true);
                } else {

                    $currentSelect.find('option').prop('disabled', false);
                }
                $currentSelect.select2('destroy').select2({
                    placeholder: $currentSelect.data('placeholder'),
                    width: '100%'
                });
            });

            $('.disable-all').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                width: '100%'
            });

            $('#date-range').flatpickr({
                mode: "range",
                maxDate: "today",
                dateFormat: "Y-m-d",
            });
        })
    </script>
@endpush
