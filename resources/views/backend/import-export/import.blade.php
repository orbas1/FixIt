@extends('backend.layouts.master')
@section('content')
    <div class="import-export">
        <div class="card tab2-card">
            <div class="card-header">

                <h5>{{ __('static.import_export') }} ({{ $tableData['name'] }})</h5>
            </div>

            <div class="card-body">
                <div class="row g-0">
                    <div class="col-xl-3">
                        <div class="vertical-tabs h-100">
                            <div class="nav nav-pills h-100" id="v-pills-tab" role="tablist">
                            @if (isset($tableData['export']))
                            <a class="nav-link active" id="subscription_plans_tab" data-bs-toggle="pill"
                                href="#subscription_plans">{{ __('static.export.export') }}</a>
                            @endif
                            @if (isset($tableData['import']))
                                <a class="nav-link " id="onboarding_screens_tab" data-bs-toggle="pill"
                                    href="#onboarding_screens">{{ __('static.import.import') }}</a>
                            @endif

                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="vertical-tabs">
                            <div class="tab-content">
                            @if (isset($tableData['import']))
                                <div class="tab-pane fade  " id="onboarding_screens">
                                    <form id="importForm" method="POST" action="{{ route($tableData['import_route'] ?? '') }}"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <ul class="nav nav-tabs" id="importMethodTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link active" id="local-file-tab" data-bs-toggle="tab"
                                                    href="#local-file" role="tab" aria-controls="local-file"
                                                    aria-selected="true">
                                                    {{ __('static.import.local_files') }}
                                                </a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="direct-link-tab" data-bs-toggle="tab"
                                                    href="#direct-link" role="tab" aria-controls="direct-link"
                                                    aria-selected="false">
                                                    {{ __('static.import.google_shit_link') }}
                                                </a>
                                            </li>
                                        </ul>

                                        <div class="tab-content" id="importMethodTabContent">
                                            <div class="tab-pane fade show active" id="local-file" role="tabpanel"
                                                aria-labelledby="local-file-tab">
                                                <div class="form-group">
                                                    <input type="file" name="fileImport-1" id="importFile"
                                                        class="form-control" style="display: none;">
                                                </div>
                                                <div class="file-upload-box">
                                                    <div class="form-group">
                                                        <div class="drop-area file-browse-button">
                                                            <img src="{{ asset('admin/images/upload.svg') }}"
                                                                class="img-fluid" />
                                                            <h5 class="file-instruction">{{ __('static.import.drag_drop') }}
                                                            </h5>
                                                            <span>{{ __('static.import.private_message') }}</span>
                                                            <button type="button"
                                                                class="btn w-100">{{ __('static.import.select_files') }}</button>
                                                            <input type="hidden" name="active_tab" value="local-file">
                                                        </div>
                                                    </div>
                                                    <input class="file-browse-input" type="file" name="fileImport" hidden>
                                                </div>
                                                <ul class="file-list h-custom-scrollbar"></ul>
                                            </div>

                                            <div class="tab-pane fade" id="direct-link" role="tabpanel"
                                                aria-labelledby="direct-link-tab">

                                                <div class="file-upload-box">
                                                    <div class="form-group">
                                                        <div class="drop-area file-browse-button">
                                                            <img src="{{ asset('admin/images/googlesheet.svg') }}" />
                                                            <h5 class="file-instruction">
                                                                {{ __('static.import.enter_link') }}</h5>
                                                            <span>{{ __('static.import.private_message') }}</span>
                                                            <div class="import-link mt-3">
                                                                <input type="text" name="google_sheet_url"
                                                                    class="form-control"
                                                                    placeholder="https://docs.google.com/spreadsheets/.."
                                                                    value="">
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <p class="common-content">*Please download the example CSV file from <a
                                                    href="{{ asset(@$tableData['example_file']) ?? '' }}" download class="text-primary fw-bold"> Here
                                                </a>and please ensure you <a href="{{ asset(@$tableData['instruction_file']) ?? '' }}" download
                                                    class="text-primary fw-bold"> read the instructions </a>carefully before initiating the
                                                import.</p>
                                        </div>

                                        <div class="d-flex justify-content-end mt-3">
                                            <button type="submit" class="btn btn-primary spinner-btn" id="importSubmitBtn">
                                                {{ __('static.import.import') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                            @if (isset($tableData['export']))
                                <div class="tab-pane fade show active " id="subscription_plans">
                                    <form id="exportForm" method="GET" action="{{ route($tableData['export_route'] ?? '') }}">
                                        <div class="form-group mb-0">
                                            <label for="exportFormat">{{ __('static.export.select_export_format') }}</label>
                                            <select id="exportFormat" name="format" class="form-select form-control">
                                                <option value="csv">{{ __('static.export.csv') }}</option>
                                                <option value="excel">{{ __('static.export.excel') }}</option>
                                            </select>
                                        </div>

                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary" id="exportBtn">{{ __('static.export.export') }}</button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            let fileListContainer = $('.file-list');
            let fileInput = $('.file-browse-input');

            $('.file-browse-button .btn').on('click', function(e) {
                e.preventDefault();
                fileInput.click();
            });

            fileInput.on('change', function() {
                displayFile(this.files[0]);
            });

            $('.drop-area').on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragging');
            }).on('dragleave', function() {
                $(this).removeClass('dragging');
            }).on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragging');
                const droppedFile = e.originalEvent.dataTransfer.files[0];
                fileInput[0].files = e.originalEvent.dataTransfer.files;
                displayFile(droppedFile);
            });

            function displayFile(file) {
                if (!file) return;

                fileListContainer.empty();

                const extension = file.name.split('.').pop().toUpperCase();
                const formattedSize = formatFileSize(file.size);

                const fileItem = `
            <li class="file-item justify-content-between">
                <div class="file-name-box">
                    <svg>
                        <use xlink:href="{{ asset('admin/images/file.svg#file') }}"></use>
                    </svg>
                    <div class="file-extension">${extension}</div>
                </div>
                <div class="file-content-wrapper">
                    <div class="file-content">
                        <h5 class="file-name mb-0">${file.name}</h5>
                        <div class="file-info">
                            <small class="file-size">${formattedSize}</small>
                            <small class="file-divider">•</small>
                            <small class="file-status">Ready</small>
                        </div>
                    </div>
                </div>
                <i class="ri-delete-bin-6-line cancel-button" title="Remove File" style="cursor: pointer;"></i>
            </li>`;
                fileListContainer.append(fileItem);
            }

            function formatFileSize(size) {
                return size >= 1048576 ?
                    `${(size / 1048576).toFixed(2)} MB` :
                    size >= 1024 ?
                    `${(size / 1024).toFixed(2)} KB` :
                    `${size} B`;
            }

            fileListContainer.on('click', '.cancel-button', function() {
                $(this).closest('.file-item').remove();
                fileInput.val('');
            });

            $('body').on('shown.bs.tab', 'a[data-bs-toggle="tab"]', function(e) {
                const activeTab = $(e.target).attr('href').replace('#', '');
                if ($('#importForm').length) {
                    $('#importForm').find('input[name="active_tab"]').val(activeTab);
                }
                console.log('Active tab:', activeTab);
            });

            $('#importForm').on('submit', function(e) {
                const activeTab = $('input[name="active_tab"]').val();
                const fileSelected = fileInput[0].files.length > 0;
                const googleSheetUrl = $('input[name="google_sheet_url"]').val().trim();

                if (activeTab === 'local-file' && !fileSelected) {
                    e.preventDefault();
                    alert('Please select a file to import.');
                    return false;
                }

                if (activeTab === 'direct-link' && googleSheetUrl === '') {
                    e.preventDefault();
                    alert('Please enter a valid Google Sheet URL.');
                    return false;
                }

                $('#importSubmitBtn').html(
                    '<span class="spinner-border spinner-border-sm"></span> Uploading...').prop(
                    'disabled', true);
            });
        });
    </script>
@endpush
