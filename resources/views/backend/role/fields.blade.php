<div class="row g-sm-4 g-3">
    <div class="col-12">
        <div class="card tab2-card">
            <div class="card-header">
                <h5>{{ isset($role) ? __('static.roles.edit') : __('static.roles.create') }}</h5>
            </div>
            <div class="card-body">
                <div class="roles">
                    <div class="form-group row">
                        <label class="col-md-2" for="name">{{ __('static.name') }}<span> *</span></label>
                        <div class="col-md-10">
                            <input class='form-control' type="text" name="name" id="name"
                                value="{{ isset($role->name) ? $role->name : old('name') }}"
                                placeholder="{{ __('static.roles.enter_name') }}">
                            @error('name')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="permission">
                    <div class="card-header">
                        <div class="form-group row">
                            <label class="col-md-2 m-0" for="name">{{ __('static.roles.permissions') }}<span>
                                    *</span></label>
                            <div class="col-md-10">
                                @error('permissions')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="permission-section">
                            <ul>
                                <li>
                                    <label for="all_permissions">
                                        <h5>{{ __('static.roles.select_all_permissions') }} &nbsp;<input type="checkbox"
                                                id="all_permissions" class="checkbox_animated"></h5>
                                    </label>
                                </li>
                            </ul>
                            @foreach ($modules as $key => $module)
                                <ul>
                                    <li>
                                        <h5 class="text-truncate module-name" data-tooltip-title="{{ ucwords(str_replace('_', ' ', $module->name)) }}">{{ ucwords(str_replace('_', ' ', $module->name)) }}:</h5>
                                    </li>

                                    @php
                                        $permissions = isset($role)
                                            ? $role->getAllPermissions()->pluck('name')->toArray()
                                            : [];
                                        $isAllSelected =
                                            count(array_diff(array_values($module->actions), $permissions)) === 0;
                                    @endphp
                                    <li>
                                        <div class="form-group m-checkbox-inline mb-0 d-flex">
                                            <label class="d-block" for="all{{ $module->name }}">

                                                <input type="checkbox"
                                                    class="checkbox_animated select-all-permission select-all-for-{{ $module->name }}"
                                                    id="all-{{ $module->name }}" value="{{ $module->name }}"
                                                    {{ $isAllSelected ? 'checked' : '' }}>{{ __('static.roles.all') }}

                                            </label>
                                        </div>
                                    </li>
                                    @foreach ($module->actions as $action => $permission)
                                        <li>
                                            <label class="d-block" for="{{ $permission }}"
                                                data-action="{{ $action }}" data-module="{{ $module->name }}">
                                                <input type="checkbox" name="permissions[]"
                                                    class="checkbox_animated module_{{ $module->name }} module_{{ $module->name }}_{{ $action }}"
                                                    value="{{ $permission }}" id="{{ $permission }}"
                                                    {{ in_array($permission, $permissions) ? 'checked' : '' }}>{{ $action }}
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button id='submitBtn' type="submit"
                        class="btn btn-primary spinner-btn ms-auto">{{ __('static.submit') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
    <script>
        $(document).ready(function() {
            'use strict';

            function initializeTooltips() {
                $('.module-name').each(function() {
                    const $element = $(this);
                    const isTruncated = $element[0].scrollWidth > $element[0].offsetWidth;

                    if (isTruncated) {
                        if (!$element.data('bs.tooltip')) {
                            $element.tooltip({
                                trigger: 'hover',
                                placement: 'bottom',
                                title: $element.attr('data-tooltip-title')
                            });
                        }
                    } else {
                        if ($element.data('bs.tooltip')) {
                            $element.tooltip('dispose');
                        }
                    }
                });
            }

            initializeTooltips();

            $(window).resize(function() {
                initializeTooltips();
            });

            $(document).on('click', '.select-all-permission', function() {
                let value = $(this).prop('value');
                $('.module_' + value).prop('checked', $(this).prop('checked'));
                updateGlobalSelectAll();
            });
            $('.checkbox_animated').not('.select-all-permission').on('change', function() {
                let $this = $(this);
                let $label = $this.closest('label');
                let module = $label.data('module');
                let action = $label.data('action');
                let total_permissions = $('.module_' + module).length;
                let $selectAllCheckBox = $this.closest('.' + module + '-permission-list').find(
                    '.select-all-permission');
                let total_checked = $('.module_' + module).filter(':checked').length;
                let isAllChecked = total_checked === total_permissions;
                if ($this.prop('checked')) {
                    $('.module_' + module + '_index').prop('checked', true);

                } else {
                    let moduleCheckboxes = $(`input[type="checkbox"][data-module="${module}"]:checked`);
                    if (moduleCheckboxes.length === 0) {
                        if (action === 'index') {
                            $('.module_' + module).prop('checked', false);
                        }
                        $(`.module_${module}_${action}`).prop('checked', false);
                        $('.select-all-for-' + module).prop('checked', false);
                    }
                }

                $('.select-all-for-' + module).prop('checked', isAllChecked);
                updateGlobalSelectAll();
            });

            $('#roleForm').validate({});
        });

        $('#all_permissions').on('change', function() {
            $('.checkbox_animated').prop('checked', $(this).prop('checked'));
        });

        function updateGlobalSelectAll() {
            let allChecked = true;
            $('.select-all-permission').each(function() {
                if (!$(this).prop('checked')) {
                    allChecked = false;
                }
            });
            $('#all_permissions').prop('checked', allChecked);
        }

        $("#role-form").validate({
            ignore: [],
            rules: {
                "name": {
                    required: true
                },
                "permissions[]": {
                    required: true
                }
            },
            errorPlacement: function (error, element) {
                if (element.attr("name") === "permissions[]") {
                    error.appendTo(element.closest('.permission').find('.col-md-10'));
                } else {
                    error.insertAfter(element);
                }
            }
        });

        $('#submitBtn').on('click', function(e) {
            $("#role-form").valid();
        });
    </script>
@endpush
