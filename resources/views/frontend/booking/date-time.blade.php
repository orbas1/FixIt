<!-- Date Time modal -->
<div class="modal fade date-time-modal" id="datetimeModal-{{$booking?->booking_number}}" tabindex="-1" aria-labelledby="datetimeModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="{{route('frontend.booking.update', $booking?->id)}}" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title" id="datetimeModalLabel-{{$booking?->booking_number}}">{{__('frontend::static.bookings.edit_date_time')}}</h3>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <div class="main-inline-calender">
                <input class="form-control form-control-gray" id="inline-calender" type="date" name="date">
              </div>
            </div>
            <div class="col-12">
              <div class="inline-time-picker">
                <p class="mb-2">{{__('frontend::static.bookings.select_time')}}</p>
                <input class="form-control form-control-gray flatpickr-input" id="time-picker-{{$booking?->booking_number}}" name="time" type="text" placeholder="{{__('frontend::static.bookings.select_time')}}" readonly="readonly">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer pt-0">
          <div class="inline-picker-btn">
            <button type="submit" class="btn btn-solid">{{__('frontend::static.bookings.done')}}</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
<!-- cancel reason modal -->
<div class="modal fade cancel-modal" id="cancelReasonModal-{{$booking?->id}}" tabindex="-1" aria-labelledby="cancelReasonModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="cancelReasonModalLabel">{{__('frontend::static.bookings.reason_of_cancel')}}</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="cancelReasonForm" action="{{route('frontend.booking.update', $booking?->id)}}" method="POST">
          @csrf
          @method('PUT')
        <div class="modal-body">
          <div class="cancel-content">
            <textarea class="form-control" id="reason" name="reason" rows="5"
              placeholder="{{__('frontend::static.bookings.write_reason_here')}}"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="booking_status" value="Cancel" />
          <button type="submit" class="btn btn-outline" data-bs-toggle="modal" data-bs-target="#cancelModal">
          {{__('frontend::static.bookings.submit')}}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@push('js')
<script>
  (function($) {
    "use strict";
    $(document).ready(function() {
      let booking_number = "{{$booking?->booking_number}}";
      flatpickr("#time-picker-" + booking_number, {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
      });
    });

    $("#cancelReasonForm").validate({
      ignore: [],
      rules: {
        "reason": "required",
      }
    });
  })(jQuery);
</script>
@endpush
