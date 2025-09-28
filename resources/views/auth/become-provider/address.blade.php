<!-- Start Add Address Modal -->
<div class="modal fade address-modal" id="addaddress" tabindex="-1" aria-labelledby="addaddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{ route('backend.address.store') }}" id="addressForm" method="post">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 mt-0" id="addaddressModalLabel">{{ __('static.address.add') }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                <div class="modal-body">
                    @csrf
                    @isset($provider)
                        <input type="hidden" name="address_type" value="provider">
                        <input type="hidden" name="id" value="{{ $provider->id }}">
                        @include('backend.address.fields')
                    @endisset
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-gray" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary submitBtn spinner-btn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End Add Address Modal -->
@isset($provider->addresses)
    @foreach ($provider->addresses as $address)
        <!-- Edit Address Modal -->
        <div class="modal fade edit-address" id="editAddress{{ $address->id }}" tabindex="-1"
            aria-labelledby="editAddressLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <form action="{{ route('backend.address.update', $address->id) }}" method="post">
                        @method('PUT')
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="editAddressLabel">{{ __('static.address.edit') }}</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            @csrf
                            @isset($provider)
                                <input type="hidden" name="address_type" value="provider">
                                <input type="hidden" name="id" value="{{ $provider->id }}">
                                @include('backend.address.fields', ['address' => $address])
                            @endisset
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-gray" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary submitBtn spinner-btn">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Delete Address Modal -->
        <div class="modal fade" id="confirmationModal{{ $address->id }}" tabindex="-1"
            aria-labelledby="confirmationModalLabel{{ $address->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-start">
                        <div class="main-img">
                            <img src="{{ asset('admin/images/svg/trash-dark.svg') }}" alt="">
                        </div>
                        <div class="text-center">
                            <div class="modal-title"> {{ __('static.delete_message') }}</div>
                            <p>{{ __('static.delete_note') }}</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('backend.address.destroy', $address->id) }}" method="post">
                            @csrf
                            @method('delete')
                            <button class="btn cancel" data-bs-dismiss="modal" type="button">{{ __('static.cancel') }}</button>
                            <button class="btn btn-primary delete spinner-btn" type="submit">{{ __('static.delete') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endisset
@push('js')
<script>
    (function($) {
        "use strict";

        $(document).ready(function() {
            $("#addressForm").validate({
                ignore: [],
                rules: {
                    "country_id": "required",
                    "state_id": "required",
                    "city": "required",
                    "area": "required",
                    "postal_code": "required",
                    "address": "required"
                }
            });
        });

    })(jQuery);
</script>
@endpush
