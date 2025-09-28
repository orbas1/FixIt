@use('App\Models\Role')
@use('App\Enums\RoleEnum')
@php
    $roles = Role::whereNot('name', RoleEnum::ADMIN)->get();
@endphp
@extends('backend.layouts.master')

@section('title', __('static.unverfied_users.unverfied_users'))

@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.unverfied_users.unverfied_users') }}</h5>
                    <div class="btn-action">
                        @can('backend.user.destroy')
                            <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                                style="display: none;" data-url="{{ route('backend.delete.users') }}">
                                <span id="count-selected-rows">0</span>{{ __('static.delete_selected') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="booking-table">
                        <div class="booking-select common-table-select">
                            <form>
                                <select class="select-2 form-control" id="userRoleFilter"
                                    data-placeholder="{{ __('static.booking.select_role') }}">
                                    <option class="select-placeholder" value=""></option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role?->name }}"
                                            @if (request()->role == $role?->name) selected @endif>{{ $role?->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="table-responsive">
                            {!! $dataTable->table() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    {!! $dataTable->scripts() !!}
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $('#userRoleFilter').change(function() {
                    console.log("cdsxc")
                    var selectedStatus = $(this).val();
                    var newUrl = "{{ route('backend.unverfied-users.index') }}";
                    if (selectedStatus) {
                        newUrl += '?role=' + selectedStatus;
                    }
                    location.href = newUrl;
                });
            });
        })(jQuery);
    </script>
@endpush
