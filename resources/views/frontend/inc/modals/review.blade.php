@isset($review)
<!-- Edit review modal -->
<div class="modal fade review-modal" id="editReviewModal-{{ $review->id }}">
  <div class="modal-dialog modal-dialog-centered">
    <form
      action="{{ route('frontend.account.review.update', $review->id) }}"
      method="post">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title" id="editReviewModalLabel-{{ $review->id }}">{{ __('frontend::static.modal.edit_review') }}
          </h3>
          <button type="button" class="btn-close"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="rate-content">
            <p>{{ __('frontend::static.modal.review_title') }}</p>
            <div class="border-dashed my-3"></div>
            <input type="hidden" name="rating"
              id="emoji-rating-{{ $review->id }}"
              class="emoji-rating" value="{{ $review->rating }}">
            <div class="form-group">
              <ul class="emoji-tab">
                <li class="emoji-icon" data-rating="1">
                  <div class="emojis">
                    <svg class="emoji deactive">
                      <use xlink:href="{{ asset('frontend/images/svg/deactive-emoji-face.svg#bad') }}"></use>
                    </svg>
                    <svg class="emoji active">
                        <use xlink:href="{{ asset('frontend/images/svg/active-emoji-face.svg#bad') }}"></use>
                    </svg>
                  </div>
                  <h4>{{ __('frontend::static.modal.bad') }}</h4>
                </li>
                <li class="emoji-icon" data-rating="2">
                  <div class="emojis">
                    <svg class="emoji deactive">
                      <use xlink:href="{{ asset('frontend/images/svg/deactive-emoji-face.svg#okay') }}"></use>
                    </svg>
                    <svg class="emoji active">
                        <use xlink:href="{{ asset('frontend/images/svg/active-emoji-face.svg#okay') }}"></use>
                    </svg>
                  </div>
                  <h4>{{ __('frontend::static.modal.okay') }}</h4>
                </li>
                <li class="emoji-icon" data-rating="3">
                  <div class="emojis">
                    <svg class="emoji deactive">
                      <use xlink:href="{{ asset('frontend/images/svg/deactive-emoji-face.svg#good') }}"></use>
                    </svg>
                    <svg class="emoji active">
                        <use xlink:href="{{ asset('frontend/images/svg/active-emoji-face.svg#good') }}"></use>
                    </svg>
                  </div>
                  <h4>{{ __('frontend::static.modal.good') }}</h4>
                </li>
                <li class="emoji-icon" data-rating="4">
                  <div class="emojis">
                    <svg class="emoji deactive">
                      <use xlink:href="{{ asset('frontend/images/svg/deactive-emoji-face.svg#amazing') }}"></use>
                    </svg>
                    <svg class="emoji active">
                        <use xlink:href="{{ asset('frontend/images/svg/active-emoji-face.svg#amazing') }}"></use>
                    </svg>
                  </div>
                  <h4>{{ __('frontend::static.modal.amazing') }}</h4>
                </li>
                <li class="emoji-icon" data-rating="5">
                  <div class="emojis">
                    <svg class="emoji deactive">
                      <use xlink:href="{{ asset('frontend/images/svg/deactive-emoji-face.svg#excellent') }}"></use>
                    </svg>
                    <svg class="emoji active">
                        <use xlink:href="{{ asset('frontend/images/svg/active-emoji-face.svg#excellent') }}"></use>
                    </svg>
                  </div>
                  <h4>{{ __('frontend::static.modal.excellent') }}</h4>
                </li>
              </ul>
            </div>
            <div class="form-group mb-0">
              <label for="rating">{{ __('frontend::static.modal.say_something_more') }}</label>
              <textarea name="description" id="rating" rows="5" class="form-control form-control-white">{{ $review->description }}</textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer pt-0">
          <button type="submit" class="btn btn-solid"
            data-bs-dismiss="modal">{{ __('frontend::static.modal.submit') }}</button>
        </div>
      </div>
    </form>
  </div>
</div>
<!-- Delete Address modal -->
<div class="modal fade delete-modal" id="deleteReviewModel-{{ $review?->id }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            <div class="modal-body text-center">
                <i class="iconsax modal-icon" icon-name="trash"></i>
                <h3>Delete Item? </h3>
                <p class="mx-auto">
                {{ __('frontend::static.modal.remove_review') }}
                </p>
            </div>
            <form action="{{ route('frontend.account.review.delete', $review->id) }}" method="post">
                @method('DELETE')
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline"
                        data-bs-dismiss="modal">{{ __('frontend::static.account.no') }}</button>
                    <button type="submit" class="btn btn-solid"
                        data-bs-toggle="modal"
                        data-bs-target="#successfullyDeleteaddressModel">{{ __('frontend::static.account.yes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



@endisset
@push('js')
<script>
  (function($) {
    "use strict";
    $(document).ready(function() {
      $(".emoji-tab .emoji-icon").on("click", function() {
        var selectedRating = $(this).data("rating");
        var reviewId = $(this).closest('.modal').find('input[name="rating"]').attr('id').split('-')[2];
        $("#emoji-rating-" + reviewId).val(selectedRating);
        $(".emoji-tab .emoji-icon").removeClass('active');
        $(this).addClass('active');
      });

      $('.review-modal').on('show.bs.modal', function() {
        var reviewId = $(this).find('input[name="rating"]').attr('id').split('-')[2];
        var currentRating = $("#emoji-rating-" + reviewId).val();
        $(".emoji-tab .emoji-icon").removeClass('active');
        $(".emoji-tab .emoji-icon[data-rating='" + currentRating + "']").addClass('active');
      });
    });
  })(jQuery);
</script>
@endpush
