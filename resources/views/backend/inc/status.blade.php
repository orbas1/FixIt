@isset($data)
    @if (isset($type) && $type == 'Page')
        @if ($data->status == 0)
            <span class="badge bg-warning text-white">{{ __('static.pending') }}</span>
        @endif
        @if ($data->status == 1)
            <span class="badge bg-success">{{ __('static.published') }}</span>
        @endif
        @if ($data->status == 2)
            <span class="badge bg-info">{{ __('static.draft') }}</span>
        @endif
    @else
        @if ($data->status == 0)
            <span class="badge bg-danger">{{ __('static.inactive') }}</span>
        @endif
        @if ($data->status == 1)
            <span class="badge bg-success">{{ __('static.active') }}</span>
        @endif
    @endif
@endisset
