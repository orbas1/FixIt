<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */
    'notification_not_found' => 'Notification not found or already read',
    'at_least_two_services_required' => 'The service package must include at least two services.',
    'notification_mark_as_read' => 'Notification marked as read',
    'please_select_a_file_smaller_than' => 'Please select a file smaller than :max',
    'additional_service_invalid_with_id' => 'The selected additional service with ID :id is invalid.',
    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute must only contain letters.',
    'alpha_dash' => 'The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute must only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'The :attribute must be between :min and :max characters.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'current_password' => 'The password is incorrect.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'The :attribute must be a valid email address.',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'string' => 'The :attribute must be greater than :value characters.',
        'array' => 'The :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'The :attribute must be greater than or equal :value.',
        'file' => 'The :attribute must be greater than or equal :value kilobytes.',
        'string' => 'The :attribute must be greater than or equal :value characters.',
        'array' => 'The :attribute must have :value items or more.',
    ],
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'numeric' => 'The :attribute must be less than :value.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'string' => 'The :attribute must be less than :value characters.',
        'array' => 'The :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'The :attribute must be less than or equal :value.',
        'file' => 'The :attribute must be less than or equal :value kilobytes.',
        'string' => 'The :attribute must be less than or equal :value characters.',
        'array' => 'The :attribute must not have more than :value items.',
    ],
    'max' => [
        'numeric' => 'The :attribute must not be greater than :max.',
        'file' => 'The :attribute must not be greater than :max kilobytes.',
        'string' => 'The :attribute must not be greater than :max characters.',
        'array' => 'The :attribute must not have more than :max items.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'The :attribute must be at least :min characters.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'multiple_of' => 'The :attribute must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'password' => 'The password is incorrect.',
    'present' => 'The :attribute field must be present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid zone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute format is invalid.',
    'uuid' => 'The :attribute must be a valid UUID.',

    'service_id_invalid' => 'The selected service_id is invalid.',
    'coupon_code_not_found' => 'We could not find an :code coupon',
    'service_ids_required' => 'At least one service ID is required.',

    'service_ids_array' => 'The service IDs must be provided as an array.',
    'service_ids_exists' => 'The service IDs must be provided as an array.',
    'is_multiple_serviceman_required' => 'The isMultipleServiceman field is required.',
    'is_multiple_serviceman_boolean' => 'The isMultipleServiceman field must be a boolean.',
    'required_servicemen_required' => 'The required_servicemen field is required when isMultipleServiceman is true.',
    'required_servicemen_integer' => 'The required_servicemen field must be an integer.',
    'select_serviceman_required' => 'The select_serviceman field is required.',
    'select_serviceman_in' => 'The selected select_serviceman is invalid. It must be either "user_choice" or "app_choose".',
    'select_date_time_required' => 'The select_date_time field is required.',
    'select_date_time_in' => 'The selected select_date_time is invalid. It must be either "custom" or "as_provider".',

    'providerId_exists' => 'Provider does Not Exists',
    'serviceId_exists' => 'Service does Not Exists',

    'user_is_not_provider' => 'User Is Not A Provider.',
    'login_type_google_apple_or_phone' => 'Login with type can be either google or apple',
    'address_ids_exists' => 'The address with ID :value does not exist.',

    'banner_images_required' => 'Please select at least one image.',
    'banner_type_required' => 'Please select Banner Type.',
    'banner_related_id_required' => 'Please select Banner Category Type.',
    'blog_categories_required' => 'The categories field is required',
    'zones_required' => 'The zones field is required',

    'commission_regex' => 'Enter commission rate percentage between 0 to 99.99',
    'category_type' => 'Category type can be either blog or service',

    'user_id_required' => 'Please select Provider.',
    'document_id_required' => 'Please select Document.',
    'identity_no_required' => 'Document Number Is Required.',

    'service_id_required' => 'The Services field is required',
    'start_end_date_required' => 'The Start Date & End Date field is required',
    'image_required' => 'Require at least one image',

    'provider_id_required' => 'The Provider field is required',
    'service_id_required_if' => 'The Related Services field is required when is random related services is Off.',
    'type' => 'Please select a service type',
    'price_required_if' => 'The price field is required',

    'type_in' => 'Tag type can be either post or product',
    'rate_regex' => 'Specify a tax rate between 0 and 99.99.',

    'provider_id_exists' => 'The selected provider is invalid.',
    'gap_required' => 'The gap field is required.',
    'gap_integer' => 'The gap must be an integer.',
    'gap_min' => 'The gap must be at least 1.',
    'time_unit_required' => 'The time unit field is required.',
    'time_unit_in' => 'The selected time unit is invalid.',
    'time_slots_required' => 'At least one time slot is required.',
    'time_slots_day_required' => 'The day field is required.',
    'time_slots_day_in' => 'The selected day is invalid.',
    'time_slots_start_time_required' => 'The start time field is required.',
    'time_slots_start_time_date_format' => 'The start time does not match the format H:i.',
    'time_slots_end_time_required' => 'The end time field is required.',
    'time_slots_end_time_date_format' => 'The end time does not match the format H:i.',
    'time_slots_end_time_after' => 'The end time must be after the start time.',

    'payment_type_in' => 'Payment type should be PayPal or bank.',

    // new keys
    'invalid_address_id' => 'Invalid address id',
    'user_not_exists' => 'User Does Not Exists or is Deactivated',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        // update profile
        'name' => [
            'max' => 'The name may not be greater than :max characters.',
        ],
        'email' => [
            'email' => 'The email must be a valid email address.',
            'unique' => 'The email has already been taken.',
        ],
        'phone' => [
            'required' => 'The phone number is required.',
            'digits_between' => 'The phone number must be between :min and :max digits.',
            'unique' => 'The phone number has already been taken.',
        ],
        'code' => [
            'required' => 'The code is required.',
        ],
        'role_id' => [
            'exists' => 'The selected role is invalid.',
        ],

        //create address
        'country_id' => [
            'required' => 'The country field is required.',
            'exists' => 'The selected country is invalid.',
        ],
        'state_id' => [
            'required' => 'The state field is required.',
            'exists' => 'The selected state is invalid.',
        ],
        'city' => [
            'required' => 'The city is required.',
            'string' => 'The city must be a string.',
        ],
        'address' => [
            'required' => 'The address is required.',
        ],
        'latitude' => [
            'required' => 'The latitude is required.',
            'latitude_longitude' => 'The latitude must be a valid latitude.',
        ],
        'longitude' => [
            'required' => 'The longitude is required.',
            'latitude_longitude' => 'The longitude must be a valid longitude.',
        ],
        'postal_code' => [
            'required' => 'The postal code is required.',
        ],
        'alternative_phone' => [
            'required_if' => 'The alternative phone is required when role type is service.',
        ],
        'alternative_name' => [
            'required_if' => 'The alternative name is required when role type is service.',
        ],

        //update address
        'user_id' => [
            'nullable' => 'The user ID is optional.',
            'exists' => 'The selected user ID is invalid.',
        ],
        'type' => [
            'required' => 'The type is required.',
            'string' => 'The type must be a string.',
        ],
        'unique_category_zone' => 'The category title must be unique within the selected zones.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
