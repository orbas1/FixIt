@extends('backend.layouts.master')

@section('title', __('static.users.users'))

@section('content')

    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.users.system_users') }}</h5>
                    <div class="btn-action">
                        @can('backend.user.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.user.create') }}" class="btn">{{ __('static.users.create') }} </a>
                            </div>
                        @endcan
                        @can('backend.user.destroy')
                            <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                                style="display: none;" data-url="{{ route('backend.delete.users') }}">
                                <span id="count-selected-rows">0</span>{{ __('static.delete_selected') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="user-table">
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
        const parentContainer = document.getElementById("user-table"); // Target parent

        for (let i = 0; i < numberOfDivs; i++) {
            const newDiv = document.createElement("div");
            newDiv.classList.add("new-class");
            newDiv.textContent = `Div ${i + 1}`;
            parentContainer.appendChild(newDiv); // Append to specific parent
        }


        // let divCounter = 0; // Track the number of divs

        // document.getElementById("addDivBtn").addEventListener("click", function() {
        //     const parentContainer = document.getElementById("user-table");
        //     const newDiv = document.createElement("div");

        //     divCounter++; // Increment counter
        //     const uniqueClass = `new-class-${divCounter}`;
        //     newDiv.classList.add(uniqueClass);
        //     newDiv.textContent = `Div ${divCounter} (Class: ${uniqueClass})`;

        //     parentContainer.appendChild(newDiv);
        // });



        (function($) {
            "use strict";

            // $('#user-table').parent('div').addClass('newClass');

            $(document).ready(function() {
                $(".credit-wallet").click(function() {
                    $("input[name='type']").val("credit");
                });

                $(".debit-wallet").click(function() {
                    $("input[name='type']").val("debit");
                });
            });

        })(jQuery);
    </script>
@endpush
