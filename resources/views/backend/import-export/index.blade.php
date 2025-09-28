@use('App\Enums\RoleEnum')
@extends('backend.layouts.master')
@section('title','Data Import & Export')

@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Data Import & Export</h5>
                    {{-- <div>
                        <div class="form-inline">
                        </div>
                    </div> --}}
                </div>

                <div class="card-body common-table">
                    <div class="table-main table-about service-table">
                        <div class="table-responsive">
                            <table class="table table-hover mt-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($allTables as $table)
                                        <tr>
                                            <td>{{ $table['name'] }}</td>

                                            <td>
                                                <div class="action-div">
                                                    @if(isset($table['import']) || isset($table['export']))
                                                        <a href="{{ route('backend.import-export.index' , ['slug' => $table['slug']]) }}" class="edit-icon">
                                                            <i data-feather="external-link"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
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

        {{-- <div class="col-12">
            <div class="card">
                
            </div>
        </div> --}}
    </div>
@endsection

@push('js')
    <script>
        (function ($) {
            "use strict";
            $('#searchButton').on('click', function () {
                if ($('#searchInput').val().length > 0) {
                    $('#cancelButton').show();
                }
            });


            $('#cancelButton').on('click', function () {
                const url = `{{ route('backend.email-template.index') }}`
                window.location.href = url;
            });


            if ($('#searchInput').val().length > 0) {
                $('#cancelButton').show();
            }

            $('#userRoleFilter').change(function () {
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
