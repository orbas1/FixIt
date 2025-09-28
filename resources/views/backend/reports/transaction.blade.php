@extends('backend.layouts.master')
@section('title', __('static.report.transaction_reports'))
@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/flatpickr.min.css') }}">
@endpush
@use('App\Enums\PaymentStatus')
@use('App\Helpers\Helpers')
@php
    $PaymentMethodList = Helpers::getPaymentMethodConfigs();
    $paymentStatus = PaymentStatus::ALL;
@endphp
@section('content')
    <div class="row category-main g-md-4 g-3">
        <form id="filterForm" method="POST" action="{{ route('backend.transaction-report.export') }}" enctype="multipart/form-data">
            @method('POST')
            @csrf
            <div class="row g-sm-4 g-3">
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
                                        <label for="transaction_type">{{ __('static.report.transaction_type') }}</label>
                                        <select class="select-2 form-control filter-dropdown disable-all" id="transaction_type" name="transaction_type[]" data-placeholder="{{ __('static.report.select_transaction_type') }}" multiple>
                                            <option value="all">{{ __('static.report.all') }}</option>
                                            <option value="booking">{{ __('static.report.booking') }}</option>
                                            <option value="wallet">{{ __('static.report.wallet') }}</option>
                                            <option value="subscription">{{ __('static.report.subscription') }}</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="payment_status">{{ __('static.report.payment_status') }}</label>
                                        <select class="select-2 form-control filter-dropdown disable-all" id="payment_status" name="payment_status[]" data-placeholder="{{ __('static.report.select_payment_status') }}" multiple>
                                            <option value="all">{{ __('static.report.all') }}</option>
                                            @foreach ($paymentStatus as $status)
                                                <option value="{{ $status }}">{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="payment_method">{{ __('static.report.payment_method') }}</label>
                                        <select class="select-2 form-control filter-dropdown disable-all" id="payment_method" name="payment_method[]" data-placeholder="{{ __('static.report.select_payment_method') }}" multiple>
                                            <option value="all">{{ __('static.report.all') }}</option>
                                            @foreach ($PaymentMethodList as $list)
                                                <option value="{{ $list['slug'] }}">{{ $list['name'] }}</option>
                                            @endforeach
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
                </div>
                <div class="col-xl-9">
                    <div class="contentbox">
                        <div class="inside">
                            <div class="contentbox-title">
                                <h3>{{ __('static.report.transaction_reports') }}</h3>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reportExportModal">{{ __('static.report.export') }} <i class="ri-upload-line"></i></button>
                            </div>
                            <div class="ride-report-table">
                                <div class="col">
                                    <div class="table-main loader-table template-table m-0 booking-report-table">
                                        <div class="table-responsive custom-scrollbar m-0">
                                            <table class="table mt-0" id="TransactionTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('static.report.tansaction_id') }}</th>
                                                        <th>{{ __('static.report.payment_method') }}</th>
                                                        <th>{{ __('static.report.payment_status') }}</th>
                                                        <th>{{ __('static.report.amount') }}</th>
                                                        <th>{{ __('static.report.type') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <div class="report-loader-wrapper" style="display:none;">
                                                        <div class="loader"></div>
                                                    </div>
                                                </tbody>
                                            </table>
                                            <nav>
                                                <ul class="pagination justify-content-center mt-0 mb-3" id="report-pagination"></ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal export-data-modal fade" id="reportExportModal">
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
                            <div class="form-group mb-0">
                                <label for="exportFormat">{{ __('static.modal.select_export_format') }}</label>
                                <select id="exportFormat" name="format" class="form-select">
                                    <option value="csv">{{ __('static.modal.csv') }}</option>
                                    <option value="excel">{{ __('static.modal.excel') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">{{ __('static.modal.close') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('static.modal.export') }}</button>
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
        // Initialize Select2 on all .select-2 elements
        $('.select-2').select2({
            placeholder: function() {
                return $(this).data('placeholder');
            },
            width: '100%'
        });

        // Function to check if filters have values
        function checkFilters() {
            let hasValue = false;
            $('.filter-dropdown').each(function() {
                if ($(this).val() && $(this).val().length > 0) {
                    hasValue = true;
                }
            });
            $('.clear-btn').toggle(hasValue);
        }

        // Check filters on page load
        checkFilters();

        // Check filters when dropdowns change
        $('.filter-dropdown').change(function() {
            checkFilters();
        });

        // Clear all dropdowns on clear button click
        $('.clear-btn').click(function() {
            location.reload();
        });

        // Fetch transaction report table
        function fetchTransactionReportTable(page = 1) {
            $('.report-loader-wrapper').show()
            let formData = $('#filterForm').serialize();
            formData += '&page=' + page;
            var $csrfToken = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '{{ route('backend.transaction-report.filter') }}',
                type: 'POST',
                data: formData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $csrfToken
                },
                success: function(response) {
                    $('#TransactionTable tbody').html(response.transactionReportTable);

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

        // Fetch table on page load
        fetchTransactionReportTable();

        // Fetch table when dropdowns change
        $('.filter-dropdown').change(function() {
            fetchTransactionReportTable();
        });

        // Pagination click handler
        $(document).on('click', '#report-pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            const page = new URLSearchParams(url.split('?')[1]).get('page');
            fetchTransactionReportTable(page);
        });

        // Disable other options when "all" is selected
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

        $('#date-range').flatpickr({
            mode: "range",
            maxDate: "today",
            dateFormat: "Y-m-d",
        });
    })
</script>
@endpush
