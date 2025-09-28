<!-- Start Add Address Modal -->
<div class="modal fade address-modal" id="locationModal" tabindex="-1" aria-labelledby="locationModal"
  aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('frontend.address.store') }}" id="addressForm" method="post">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="addaddressModalLabel">{{ __('static.address.add') }}</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @csrf
          @include('frontend.address.fields')
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" data-bs-dismiss="modal">{{ __('frontend::static.account.close') }}</button>
          <button type="submit" class="btn btn-solid submitBtn spinner-btn">{{ __('frontend::static.account.submit') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>
