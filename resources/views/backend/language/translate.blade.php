@extends('backend.layouts.master')

@section('title', __('static.language.translate'))

@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.language.translate') }}</h5>
                </div>
                <div class="card-body">
                    <form class=""
                        action="{{ route('backend.systemLang.translate.update', ['locale' => request()->locale, 'file' => $file]) }}"
                        method="POST">
                        @csrf
                        @method('POST')
                        <div class="form-group row">
                            <label class="col-md-2"
                                for="locale">{{ __('static.language.select_translate_file') }}</label>
                            <div class="col-md-10">
                                <select class="form-select select-2" name="file" id="file-select"
                                    onchange="updateURL()">
                                    data-placeholder="{{ __('Select Locale') }}">
                                    <option></option>
                                    @foreach ($allFiles as $fileName)
                                        <option value="{{ $fileName }}"
                                            @if ($fileName === @$file) selected @endif>
                                            {{ ucfirst($fileName) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('locale')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="table-responsive language-table custom-scroll">
                            <table class="">
                                <thead>
                                    <tr>
                                        <th>
                                            {{ __('static.key') }}
                                        </th>
                                        <th>
                                            {{ __('static.value') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($translations as $key => $value)
                                        @include('backend.language.trans-fields', [
                                            'key' => $key,
                                            'value' => $value,
                                        ])
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div>
                            {{ $translations->links() }}
                        </div>
                        <div class="text-end mt-3">
                            <button id='submitBtn' type="submit"
                                class="btn btn-primary ms-auto">{{ __('static.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        "use strict";

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('file-select').addEventListener('change', updateURL);
        });

        function updateURL() {
            const file = document.getElementById('file-select').value;
            const url = `{{ route('backend.systemLang.translate', ['locale' => 'LOCALE', 'file' => 'FILE']) }}`
                .replace('LOCALE', `{{ request()?->locale }}`)
                .replace('FILE', file);

            window.location.href = url;
        }
    </script>
@endpush
