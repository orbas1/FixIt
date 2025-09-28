@extends('backend.layouts.master')
@section('title', __('static.report.provider_reports'))
@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/flatpickr.min.css') }}">
@endpush
@use('App\Enums\UserTypeEnum')
@use('App\Helpers\Helpers')
@php
    $providers = Helpers::getAllVerifiedProviders();
    $zones = Helpers::getAllZones();
    $types = UserTypeEnum::ALL;
@endphp
@section('content')
    <div class="row category-main g-md-4 g-3">
        <form id="filterForm" method="POST" action="{{ route('backend.provider-report.export') }}"
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
                                
                                <div class="form-group">
                                    <label for="provider">{{ __('static.report.provider') }}</label>
                                    <select class="select-2 form-control filter-dropdown disable-all" id="provider" name="provider[]" multiple data-placeholder="{{ __('static.report.select_provider') }}">
                                        <option value="all">{{ __('static.report.all') }}</option>
                                        @foreach ($providers as $provider)
                                            <option value="{{ $provider->id }}" sub-title="{{ $provider->email }}"
                                                image="{{ $provider?->profile_image?->original_url }}">
                                                {{ $provider->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="zone">{{ __('static.report.zone') }}</label>
                                    <select class="select-2 form-control filter-dropdown disable-all" id="zone" name="zone[]" multiple data-placeholder="{{ __('static.report.select_zone') }}">
                                        <option value="all">{{ __('static.report.all') }}</option>
                                        @foreach ($zones as $key => $zone)
                                            <option value="{{ $zone->id }}">
                                                {{ $zone->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="vehicle_type">{{ __('static.report.type') }}</label>
                                    <select class="select-2 form-control filter-dropdown disable-all" id="type[]" name="type[]" multiple data-placeholder="{{ __('static.report.select_type') }}">
                                        <option value="all">{{ __('static.report.all') }}</option>
                                        @forelse ($types as $type)
                                            <option value="{{ $type }}">{{ strtoupper($type) }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="start_end_date">{{ __('static.report.select_date') }}</label>
                                    <input type="text" class="form-control filter-dropdown" id="date-range" name="start_end_date" placeholder="{{ __('static.service_package.select_date') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="contentbox">
                        <div class="inside">
                            <div class="contentbox-title">
                                <h3>{{ __('static.report.provider_reports') }}</h3>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reportExportModal"> {{ __('static.report.export') }}
                                    <i class="ri-upload-line"></i>
                                </button>
                            </div>

                            <div class="tag-table">
                                <div class="col">
                                    <div
                                        class="table-main booking-report-table template-table provider-report-table loader-table m-0">
                                        <div class="table-responsive  custom-scrollbar m-0">
                                            <table class="table" id="providerTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('static.report.provider') }}</th>
                                                        <th>{{ __('static.report.email') }}</th>
                                                        <th>{{ __('static.report.type') }}</th>
                                                        <th>{{ __('static.report.ratings') }}</th>
                                                        <th>{{ __('static.report.earnings') }}</th>
                                                        <th>{{ __('static.report.pending_bookings') }}</th>
                                                        <th>{{ __('static.report.completed_bookings') }}</th>
                                                        <th>{{ __('static.report.cancelled_bookings') }}</th>
                                                        <th>{{ __('static.report.total_bookings') }}</th>
                                                        <th>{{ __('static.report.total_servicemen') }}</th>
                                                        <th>{{ __('static.report.total_services') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <div class="report-loader-wrapper" style="display:none;">
                                                        <div class="loader"></div>
                                                    </div>
                                                </tbody>
                                            </table>

                                            <nav>
                                                <ul class="pagination justify-content-center mt-3" id="report-pagination">
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="reportExportModal">
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
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{{ __('static.modal.close') }}</button>
                            <button type="submit" class="btn btn-primary spinner-btn">{{ __('static.modal.export') }}</button>
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

            fetchProviderReportTable(page = 1);

            $('.filter-dropdown').change(function() {
                fetchProviderReportTable();
            })

            function fetchProviderReportTable(page = 1, orderby = '', order = '') {
                $('.report-loader-wrapper').show()
                let formData = $('#filterForm').serialize();
                formData += '&page=' + page;
                if (orderby) {
                    formData += '&orderby=' + orderby;
                }
                if (order) {
                    formData += '&order=' + order;
                }

                var $csrfToken = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    url: '{{ route('backend.provider-report.filter') }}',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $csrfToken
                    },
                    success: function(response) {
                        $('#providerTable tbody').html(response.providerReportTable);
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

                fetchProviderReportTable(page);
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
