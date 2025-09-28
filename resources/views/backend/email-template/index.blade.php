@use('App\Enums\RoleEnum')
@extends('backend.layouts.master')
@section('title', __('static.email_templates.email_templates'))

@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('static.email_templates.email_templates') }}</h5>
                    <div>
                        <div class="form-inline">
                            <form action="{{ route('backend.email-template.index') }}" method="GET" class="d-flex"
                                id="searchForm">
                                <input type="text" id="searchInput" class="form-control me-2" name="search"
                                    placeholder="Search templates" value="{{ request()->get('search') }}">

                                <!-- Dropdown for selecting filter options -->
                                <select class="form-select me-2 card-head-select" name="role" id="userRoleFilter">
                                    @foreach ([RoleEnum::ADMIN, RoleEnum::CONSUMER, RoleEnum::PROVIDER] as $role)
                                        <option value="{{ $role }}"
                                            {{ request()->get('role') == $role ? 'selected' : '' }}>
                                            {{ $role }}
                                        </option>
                                    @endforeach
                                </select>

                                <button type="submit" class="btn btn-primary" id="searchButton">
                                    <i data-feather="search"></i>
                                </button>
                                <button type="button" class="btn btn-secondary ms-2" id="cancelButton"
                                    style="display:none;">Cancel</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="table-main table-about service-table">
                        <div class="table-responsive">
                            <table class="table table-hover mt-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($emailTemplates as $emailTemplate)
                                        @foreach ($emailTemplate['templates'] as $template)
                                            <tr>
                                                <td>{{ $template['name'] }}</td>
                                                <td>{{ $template['description'] }}</td>
                                                <td>
                                                    <!-- <a href="{{ route('backend.email-template.edit', ['slug' => $template['slug']]) }}"
                                                                                                class="btn btn-link text-primary" title="Edit">
                                                                                                Edit
                                                                                            </a> -->

                                                    <div class="action-div">
                                                        <a href="{{ route('backend.email-template.edit', ['slug' => $template['slug']]) }}"
                                                            class="edit-icon">
                                                            <i data-feather="edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No templates available.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="contentbox">
        <div class="inside">
            <div class="contentbox-title mb-4 d-flex justify-content-between align-items-center">
                <h3>{{ __('static.email_templates.email_templates') }}</h3>
                <div>
                    <div class="form-inline">
                        <form action="{{ route('backend.email-template.index') }}" method="GET" class="d-flex"
                            id="searchForm">
                            <input type="text" id="searchInput" class="form-control me-2" name="search"
                                placeholder="Search templates" value="{{ request()->get('search') }}">

                            <!-- Dropdown for selecting filter options -->
                            <select class="form-select me-2 " name="role" id="userRoleFilter">
                                @foreach ([RoleEnum::ADMIN, RoleEnum::CONSUMER, RoleEnum::PROVIDER] as $role)
                                    <option value="{{ $role }}"
                                        {{ request()->get('role') == $role ? 'selected' : '' }}>
                                        {{ $role }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-primary" id="searchButton">
                                <i data-feather="search"></i>
                            </button>
                            <button type="button" class="btn btn-secondary ms-2" id="cancelButton"
                                style="display:none;">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="table-main table-about service-table">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($emailTemplates as $emailTemplate)
                                @foreach ($emailTemplate['templates'] as $template)
                                    <tr>
                                        <td>{{ $template['name'] }}</td>
                                        <td>{{ $template['description'] }}</td>
                                        <td>
                                            <!-- <a href="{{ route('backend.email-template.edit', ['slug' => $template['slug']]) }}"
                                                                            class="btn btn-link text-primary" title="Edit">
                                                                            Edit
                                                                        </a> -->

                                            <div class="action-div">
                                                <a href="{{ route('backend.email-template.edit', ['slug' => $template['slug']]) }}"
                                                    class="edit-icon">
                                                    <i data-feather="edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No templates available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> --}}
@endsection

@push('js')
    <script>
        (function($) {
            "use strict";
            $('#searchButton').on('click', function() {
                if ($('#searchInput').val().length > 0) {
                    $('#cancelButton').show();
                }
            });


            $('#cancelButton').on('click', function() {
                const url = `{{ route('backend.email-template.index') }}`
                window.location.href = url;
            });


            if ($('#searchInput').val().length > 0) {
                $('#cancelButton').show();
            }

            $('#userRoleFilter').change(function() {
                var selectedRole = $(this).val();
                var newUrl = "{{ route('backend.email-template.index') }}";
                if (selectedRole) {
                    newUrl += '?role=' + selectedRole;
                }

                location.href = newUrl;
            });


        })(jQuery);
    </script>
@endpush
