@extends('backend.layouts.master')

@section('title', __('static.plan.all'))

@section('content')
<div class="row g-sm-4 g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('static.plan.all') }}</h5>
                <div class="btn-action">
                @can('backend.plan.create')
                    <div class="btn-popup mb-0">
                        <a href="{{ route('backend.plan.create') }}" class="btn">{{ __('static.plan.create') }}
                        </a>
                    </div>
                @endcan
                @can('backend.plan.destroy')
                    <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn" style="display: none;" data-url="{{ route('backend.delete.plans') }}">
                        <span id="count-selected-rows">0</span>{{ __('static.delete_selected') }}
                    </a>
                </div>
                @endcan
            </div>

            <div class="card-body common-table">
                <div class="customer-table">
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
         $(document).ready(function() {
            $('.toggle-status').click(function() {
                var toggleId = $(this).data('id');
                $('#ConfirmationModal' + toggleId).modal('show');
                return false;
            });
        });
    </script>
@endpush
