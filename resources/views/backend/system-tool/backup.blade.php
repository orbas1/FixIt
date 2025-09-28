@extends('backend.layouts.master')

@section('title', __('static.system_tools.backup'))

@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.system_tools.backup') }}</h5>
                    @can('backend.backup.create')
                        <button type="button" id="add-backup"
                            class="btn btn-primary">{{ __('static.system_tools.create_backup') }}</button>
                    @endcan
                </div>
                <div class="card-body common-table">
                    <div class="tag-table">
                        <div class="table-responsive">
                            <div class="table-main email-template-table template-table m-0">
                                <div class="table-responsive custom-scrollbar m-0">
                                    <table class="table m-0">
                                        <thead>
                                            <tr>
                                                <th>{{ __('static.system_tools.title') }}</th>
                                                <th>{{ __('static.system_tools.description') }}</th>
                                                <th>{{ __('static.created_at') }}</th>
                                                <th>{{ __('static.system_tools.action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($backups as $backup)
                                                <tr>
                                                    <td>{{ $backup->title }}</td>
                                                    <td>{{ $backup->description }}</td>
                                                    <td>{{ $backup->created_at->format('Y-m-d h:i:s A') }}</td>
                                                    <td>
                                                        <div class="action-div">
                                                            @if (!empty($backup->file_path['db']))
                                                                <a href="{{ $backup->file_path['db'] }}"
                                                                    class="edit-icon" data-bs-toggle="tooltip"
                                                                    title="Files" download>
                                                                    <i class="ri-file-download-line"
                                                                        alt="no-data"></i>
                                                                </a>
                                                            @endif
                                                            @if (!empty($backup->file_path['files']))
                                                                <a href="{{ $backup->file_path['files'] }}"
                                                                    class="edit-icon" data-bs-toggle="tooltip"
                                                                    title="Files" download>
                                                                    <i class="ri-file-download-line"
                                                                        alt="no-data"></i>
                                                                </a>
                                                            @endif
                                                            @if (!empty($backup->file_path['media']))
                                                                <a href="{{ route('backend.backup.downoadUploadsBackup', $backup->id) }}"
                                                                    class="edit-icon" data-bs-toggle="tooltip"
                                                                    title="Media">
                                                                    <i class="ri-folder-download-line"
                                                                        alt="no-data"></i>
                                                                </a>
                                                            @endif
                                                            @if (!empty($backup->file_path['db']) && !empty($backup->file_path['media']))
                                                                <a href="javascript:void(0)" class="edit-icon"
                                                                    data-bs-toggle="tooltip" title="Restore"
                                                                    onclick="showRestoreModal('{{ route('backend.backup.restoreBackup', $backup->id) }}')">
                                                                    <i class="ri-arrow-turn-forward-line"
                                                                        alt="no-data"></i>
                                                                </a>
                                                            @endif
                                                            @if (!empty($backup->file_path))
                                                                <a href="javascript:void(0)" class="delete-svg"
                                                                    data-bs-toggle="tooltip" title="Delete Backup"
                                                                    onclick="showDeleteModal('{{ route('backend.backup.deleteBackup', $backup->id) }}')">
                                                                    <i class="ri-delete-bin-line"
                                                                        alt="no-data"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade confirmation-modal" id="confirmation">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header text-center">
                    <h5 class="modal-title m-0">{{ __('static.system_tools.create_backup') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                <form action="{{ route('backend.backup.store') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body text-start backup-form">
                        <div class="form-group">
                            <label>{{ __('static.system_tools.title') }}</label>
                            <input type="text" id="title" class="form-control" name="title" placeholder=" ">
                            @error('title')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="description">{{ __('static.system_tools.description') }}</label>
                            <textarea id="floating-name" class="form-control" rows="3" name="description" placeholder="" cols="80"></textarea>
                            @error('description')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong></strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <label for="backup_type">{{ __('static.system_tools.backup_type') }}</label>
                            <select class="form-control select-2" name="backup_type" id="backup_type">
                                <option value="db">{{ __('static.system_tools.db') }}</option>
                                <option value="media">{{ __('static.system_tools.media') }}</option>
                                <option value="files">{{ __('static.system_tools.files') }}</option>
                                <option value="both">{{ __('static.system_tools.both') }}</option>
                            </select>
                            @error('backup_type')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="save" class="btn btn-primary spinner-btn">{{ __('static.submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-start">
                    <div class="main-img">
                        <i data-feather="trash-2"></i>
                    </div>
                    <div class="text-center">
                        <h4 class="modal-title">{{ __('static.system_tools.confirm_delete_backup') }}</h4>
                        <p class="mb-0">{{ __('static.system_tools.delete_backup_warning_message') }}</p>
                    </div>
                </div>

                <div class="modal-footer">
                    <input type="hidden" id="inputType" name="type" value="">
                    <form id="deleteForm" class="w-100" action="" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn cancel" data-bs-dismiss="modal">
                            {{ __('static.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary delete spinner-btn">
                            {{ __('static.delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Restore Modal -->
    <div class="modal fade restore-modal" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-start">
                    <div class="main-img">
                        <i data-feather="info"></i>
                    </div>
                    <div class="text-center">
                        <h4 class="modal-title">{{ __('static.system_tools.confirm_restore_backup') }}</h4>
                        <p class="mb-0">{{ __('static.system_tools.restore_backup_warning_message') }}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="inputType" name="type" value="">
                    <form id="restoreForm" class="w-100" action="" method="POST">
                        @csrf
                        @method('GET')
                        <button type="button" class="btn cancel" data-bs-dismiss="modal">{{ __('static.cancel') }}</button>
                        <button type="submit" class="btn btn-primary delete spinner-btn">{{ __('static.submit') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script>
        $(document).ready(function() {
            $('#add-backup').on('click', function() {

                var myModal = new bootstrap.Modal(document.getElementById("confirmation"), {});
                myModal.show();
            });
        });

        // Show Restore Modal
        function showRestoreModal(restoreUrl) {
            $('#restoreForm').attr('action', restoreUrl);
            $('#restoreModal').modal('show');
        }

        // Show Delete Modal
        function showDeleteModal(deleteUrl) {
            $('#deleteForm').attr('action', deleteUrl);
            $('#deleteModal').modal('show');
        }
    </script>
@endpush
