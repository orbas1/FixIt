 @extends('backend.layouts.master')

 @section('title', __('static.customer.customers'))

 @section('content')

     <div class="row g-sm-4 g-3">
         <div class="col-12">
             <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.customer.customers') }}</h5>
                    <div class="btn-action">
                        @can('backend.customer.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.customer.create') }}"
                                    class="btn">{{ __('static.customer.create') }}
                                </a>
                            </div>
                        @endcan
                        @can('backend.customer.destroy')
                            <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                                style="display: none;" data-url="{{ route('backend.delete.customers') }}">
                                <span id="count-selected-rows">0</span>{{ __('static.delete_selected') }}
                            </a>
                        @endcan
                    </div>
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
    (function($) {
        "use strict";

        $(document).ready(function() {
            $('.toggle-status').click(function() {
                var toggleId = $(this).data('id');
                $('#ConfirmationModal' + toggleId).modal('show');
                return false;
            });
        });
    })(jQuery);
</script>
@endpush
