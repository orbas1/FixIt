@extends('backend.layouts.master')
@section('content')

@if (isset($export))
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{ __('static.export.export') }}</h5>
        </div>
        <div class="card-body">
            <form id="exportForm" class="m-0" method="GET" action="{{ route($export_route ?? '') }}">
                <div class="form-group mb-3">
                    <label for="exportFormat">{{ __('static.export.select_export_format') }}</label>
                    <select id="exportFormat" name="format" class="form-select">
                        <option value="csv">{{ __('static.export.csv') }}</option>
                        <option value="excel">{{ __('static.export.excel') }}</option>
                    </select>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary w-100" id="exportBtn">
                        {{ __('static.export.export') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
