<div class="form-group row">
    <label class="col-md-2" for="provider_id">{{ __('static.provider_time_slot.provider') }}<span> *</span></label>
    <div class="col-md-10 error-div select-dropdown">
        <select class="select-2 form-control user-dropdown" name="provider_id" data-placeholder="{{ __('static.provider_time_slot.select_provider') }}" required>
            <option class="select-placeholder" value=""></option>
            @foreach ($providers as $key => $option)
                <option value="{{ $option?->id }}" sub-title="{{ $option?->email }}" image="{{ $option->getFirstMedia('image')?->getUrl() }}" {{ old('provider_id', @$timeSlot?->provider_id) === $option->id ? 'selected' : '' }}>
                    {{ $option->name }}
                </option>
            @endforeach
        </select>
        @error('provider_id')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
@php
    $hours = [];
    for ($i = 0; $i < 24; $i++) {
        $time = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
        $hours[] = $time;
    }

    $days = ['MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY'];
@endphp
<div class="time-slot-wrapper">
    @foreach($days as $day)
        <div class="card time-slot-card">
            <div class="position-relative">
                <input type="checkbox" class="checkbox_animated" name="checkAll" value="1">
                <label for="time-slots">{{ ucfirst(strtolower($day)) }}</label>
                
                <div class="ms-auto d-flex">
                    <label class="switch">
                        <input class="form-check-input" id="time-slots" type="checkbox" value="1" name="time_slots[{{ $day }}][is_active]" {{ !isset($timeSlots[$day]) || (isset($timeSlots[$day]) && $timeSlots[$day]['is_active']) ? 'checked' : '' }}>
                        <span class="switch-state"></span>
                    </label>
                </div>
            </div>

            <div class="time-slot-main-box">
                @foreach($hours as $hour)
                    <div class="time-slot-box">
                        <input type="checkbox" name="time_slots[{{ $day }}][slots][]" value="{{ $hour }}" {{ in_array($hour, $timeSlots[$day]['slots'] ?? []) ? 'checked' : '' }}>
                        <label>{{ $hour }}</label>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

@push('js')
    <script>
        $(document).ready(function () {
            // Enable/disable time slot checkboxes based on is_active toggle
            $('input[type="checkbox"][name*="[is_active]"]').each(function () {
                const $activeCheckbox = $(this);
                const day = $activeCheckbox.attr('name').match(/time_slots\[(.*?)\]/)[1];
                toggleSlotsForDay(day, $activeCheckbox.is(':checked'));
            });

            $('input[type="checkbox"][name*="[is_active]"]').on('change', function () {
                const day = $(this).attr('name').match(/time_slots\[(.*?)\]/)[1];
                toggleSlotsForDay(day, $(this).is(':checked'));
            });

            function toggleSlotsForDay(day, isActive) {
                $(`input[name="time_slots[${day}][slots][]"]`).prop('disabled', !isActive);
            }

            $('.time-slot-card').each(function () {
                const $card = $(this);
                const $checkAll = $card.find('input[name="checkAll"]');
                const $slotCheckboxes = $card.find('input[name^="time_slots"][name$="[slots][]"]');

                let allChecked = $slotCheckboxes.length && $slotCheckboxes.filter(':checked').length === $slotCheckboxes.length;
                $checkAll.prop('checked', allChecked);

                $checkAll.on('change', function () {
                    if ($card.find('input[type="checkbox"][name*="[is_active]"]').is(':checked')) {
                        $slotCheckboxes.prop('checked', this.checked);
                    }
                });

                $slotCheckboxes.on('change', function () {
                    let allChecked = $slotCheckboxes.length && $slotCheckboxes.filter(':checked').length === $slotCheckboxes.length;
                    $checkAll.prop('checked', allChecked);
                });
            });
        });
    </script>
@endpush
