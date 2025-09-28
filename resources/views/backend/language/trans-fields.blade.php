@if (is_array($value))
@foreach ($value as $subKey => $subValue)
  @include('backend.language.trans-fields', ['key' => "{$key}__{$subKey}", 'value' => $subValue])
@endforeach
@else
<tr>
  <td>{{ str_replace('__', '.', $key) }}</td>
  <td><input type="text" class="form-control" name="{{ $key }}" value="{{ $value }}"></td>
</tr>
@endif
