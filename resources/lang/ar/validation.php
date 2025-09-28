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

    'accepted' => 'يجب قبول السمة.',
    'active_url' => ': السمة ليست عنوان URL صالح.',
    'after' => 'يجب أن تكون السمة تاريخًا بعد: التاريخ.',
    'after_or_equal' => 'يجب أن تكون السمة تاريخًا بعد أو تساوي: التاريخ.',
    'alpha' => 'يجب أن تحتوي السمة على رسائل فقط.',
    'alpha_dash' => 'يجب أن تحتوي السمة فقط على رسائل وأرقام وشرطات وترسيح.',
    'alpha_num' => 'يجب أن تحتوي السمة فقط على رسائل وأرقام.',
    'array' => 'يجب أن تكون السمة صفيفًا.',
    'before' => 'يجب أن تكون السمة تاريخًا سابقًا: التاريخ.',
    'before_or_equal' => 'يجب أن تكون السمة تاريخًا قبل أو يساوي: التاريخ.',
    'between' => [
        'numeric' => 'يجب أن تكون السمة بين: Min و: Max.',
        'file' => 'يجب أن تكون السمة بين: Min و: Max Kilobytes.',
        'string' => 'يجب أن تكون السمة بين: min و: أقصى أحرف.',
        'array' => 'يجب أن يكون: السمة بين: min و: max العناصر.',
    ],
    'boolean' => 'يجب أن يكون حقل السمة صحيحًا أو خطأ.',
    'confirmed' => ': تأكيد السمة لا يتطابق.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => 'السمة: ليست تاريخ صالح.',
    'date_equals' => 'يجب أن يكون السمة تاريخًا يساوي: التاريخ.',
    'date_format' => 'السمة لا تتطابق مع التنسيق: التنسيق.',
    'different' => 'The: السمة و: الأخرى يجب أن تكون مختلفة.',
    'digits' => 'يجب أن تكون السمة: أرقام الأرقام.',
    'digits_between' => 'يجب أن تكون السمة بين: Min و: Max Digits.',
    'dimensions' => 'السمة لها أبعاد صورة غير صالحة.',
    'distinct' => 'حقل السمة: له قيمة مكررة.',
    'email' => 'يجب أن تكون السمة عنوان بريد إلكتروني صالح.',
    'ends_with' => 'يجب أن تنتهي السمة بأحد ما يلي:: القيم.',
    'exists' => 'المحدد: السمة غير صالحة.',
    'file' => 'يجب أن تكون السمة ملفًا.',
    'filled' => 'يجب أن يكون لحقل السمة قيمة.',
    'gt' => [
        'numeric' => 'يجب أن تكون السمة أكبر من: القيمة.',
        'file' => 'يجب أن تكون السمة أكبر من: قيمة كيلوبايت.',
        'string' => 'يجب أن تكون السمة أكبر من: أحرف القيمة.',
        'array' => 'يجب أن تحتوي السمة على أكثر من: عناصر القيمة.',
    ],
    'gte' => [
        'numeric' => 'يجب أن تكون السمة أكبر من أو متساوية: القيمة.',
        'file' => 'يجب أن تكون السمة أكبر من أو متساوية: كيلوبايت القيمة.',
        'string' => 'يجب أن تكون السمة أكبر من أو متساوية: أحرف القيمة.',
        'array' => 'يجب أن يكون لدى السمة: عناصر القيمة أو أكثر.',
    ],
    'image' => 'يجب أن تكون السمة صورة.',
    'in' => 'المحدد: السمة غير صالحة.',
    'in_array' => ': حقل السمة غير موجود في: آخر.',
    'integer' => 'يجب أن تكون السمة عددًا صحيحًا.',
    'ip' => 'يجب أن تكون السمة عنوان IP صالحًا.',
    'ipv4' => 'يجب أن تكون السمة عنوان IPv4 صالح.',
    'ipv6' => 'يجب أن تكون السمة عنوان IPv6 صالح.',
    'json' => 'يجب أن تكون السمة سلسلة JSON صالحة.',
    'lt' => [
        'numeric' => 'يجب أن تكون السمة أقل من: القيمة.',
        'file' => 'يجب أن تكون السمة أقل من: قيمة الكيلوغرام.',
        'string' => 'يجب أن تكون السمة أقل من: أحرف القيمة.',
        'array' => 'يجب أن تحتوي السمة على أقل من: عناصر القيمة.',
    ],
    'lte' => [
        'numeric' => 'يجب أن تكون السمة أقل من أو متساوية: القيمة.',
        'file' => 'يجب أن تكون السمة أقل من أو متساوية: قيمة كيلو بايت.',
        'string' => 'يجب أن تكون السمة أقل من أو متساوية: أحرف القيمة.',
        'array' => 'يجب ألا تحتوي السمة على أكثر من: عناصر القيمة.',
    ],
    'max' => [
        'numeric' => 'يجب ألا تكون السمة أكبر من: Max.',
        'file' => 'يجب ألا تكون السمة أكبر من: الحد الأقصى كيلوبايت.',
        'string' => 'يجب ألا تكون السمة أكبر من: أقصى أحرف.',
        'array' => 'يجب ألا تحتوي السمة على أكثر من: Max Attems.',
    ],
    'mimes' => 'يجب أن تكون السمة ملف من النوع :: قيم.',
    'mimetypes' => 'يجب أن تكون السمة ملف من النوع :: قيم.',
    'min' => [
        'numeric' => 'يجب أن تكون السمة على الأقل: دقيقة.',
        'file' => 'يجب أن تكون السمة على الأقل: دقيقة كيلوبايت.',
        'string' => 'يجب أن تكون السمة على الأقل: أحرف دقيقة.',
        'array' => 'يجب أن يكون السمة على الأقل: عناصر دقيقة.',
    ],
    'multiple_of' => 'يجب أن تكون السمة مضاعفة: القيمة.',
    'not_in' => 'المحدد: السمة غير صالحة.',
    'not_regex' => ': تنسيق السمة غير صالح.',
    'numeric' => 'يجب أن تكون السمة رقمًا.',
    'password' => 'كلمة المرور غير صحيحة.',
    'present' => 'يجب أن يكون حقل السمة موجودًا.',
    'regex' => ': تنسيق السمة غير صالح.',
    'required' => 'مطلوب: حقل السمة.',
    'required_if' => 'الحقل: حقل السمة مطلوب عندما: الآخر هو: القيمة.',
    'required_unless' => 'مطلوب حقل السمة ما لم يكن: الآخر في: القيم.',
    'required_with' => 'حقل السمة مطلوب عندما: القيم موجودة.',
    'required_with_all' => 'حقل السمة مطلوب عندما: القيم موجودة.',
    'required_without' => ': حقل السمة مطلوب عندما: القيم غير موجودة.',
    'required_without_all' => 'حقل السمة مطلوب عندما لا يوجد أي من: القيم موجودة.',
    'prohibited' => 'حقل السمة محظور.',
    'prohibited_if' => 'حقل السمة محظور عندما: الآخر هو: القيمة.',
    'prohibited_unless' => 'حقل: حقل السمة محظور ما لم: آخر هو في: القيم.',
    'same' => 'The: السمة و: الآخر يجب أن يتطابق.',
    'size' => [
        'numeric' => 'يجب أن تكون السمة: الحجم.',
        'file' => 'يجب أن تكون السمة: حجم كيلوبايت.',
        'string' => 'يجب أن تكون السمة: أحرف الحجم.',
        'array' => 'يجب أن تحتوي السمة على: عناصر الحجم.',
    ],
    'starts_with' => 'يجب أن تبدأ السمة بواحدة مما يلي:: القيم.',
    'string' => 'يجب أن تكون السمة سلسلة.',
    'timezone' => 'يجب أن تكون السمة منطقة صالحة.',
    'unique' => 'السمة قد أخذت بالفعل.',
    'uploaded' => 'فشل: السمة في التحميل.',
    'url' => ': تنسيق السمة غير صالح.',
    'uuid' => 'يجب أن تكون السمة uuid صالحة.',

    'service_id_invalid' => 'Service_id المحدد غير صالح.',
    'coupon_code_not_found' => 'لم نتمكن من العثور على: قسيمة رمز',
    'service_ids_required' => 'مطلوب معرف خدمة واحد على الأقل.',

    'service_ids_array' => 'يجب توفير معرفات الخدمة كصفيف.',
    'service_ids_exists' => 'يجب توفير معرفات الخدمة كصفيف.',
    'is_multiple_serviceman_required' => 'مطلوب حقل IsMultipleserviceman.',
    'is_multiple_serviceman_boolean' => 'يجب أن يكون حقل IsMultipleserviceman من منطقية.',
    'required_servicemen_required' => 'مطلوب حقل مطلوب _servicemen عندما يكون iSmultipleserviceman صحيحًا.',
    'required_servicemen_integer' => 'يجب أن يكون حقل المطلوب _servicemen عددًا صحيحًا.',
    'select_serviceman_required' => 'مطلوب حقل Select_Serviceman.',
    'select_serviceman_in' => 'محدد Select_serviceman غير صالح.يجب أن يكون إما "user_choice" أو "app_choose".',
    'select_date_time_required' => 'حقل Select_Date_Time مطلوب.',
    'select_date_time_in' => 'المحدد Select_date_time غير صالح.يجب أن يكون إما "مخصص" أو "AS_Provider".',

    'providerId_exists' => 'المزود لا يوجد',
    'serviceId_exists' => 'الخدمة لا توجد',

    'user_is_not_provider' => 'المستخدم ليس مزودًا.',
    'login_type_google_apple_or_phone' => 'يمكن أن يكون تسجيل الدخول بالنوع إما Google أو Apple',
    'address_ids_exists' => 'العنوان مع معرف: القيمة غير موجودة.',

    'banner_images_required' => 'الرجاء تحديد صورة واحدة على الأقل.',
    'banner_type_required' => 'الرجاء تحديد نوع اللافتة.',
    'banner_related_id_required' => 'يرجى تحديد نوع فئة Banner.',
    'blog_categories_required' => 'حقل الفئات مطلوب',
    'zones_required' => 'حقل المناطق مطلوب',

    'commission_regex' => 'أدخل نسبة معدل العمولة بين 0 إلى 99.99',
    'category_type' => 'يمكن أن يكون نوع الفئة إما مدونة أو خدمة',

    'user_id_required' => 'الرجاء تحديد موفر.',
    'document_id_required' => 'الرجاء تحديد المستند.',
    'identity_no_required' => 'رقم المستند مطلوب.',

    'service_id_required' => 'حقل الخدمات مطلوب',
    'start_end_date_required' => 'مطلوب حقل تاريخ البدء وحقل تاريخ الانتهاء',
    'image_required' => 'تتطلب صورة واحدة على الأقل',

    'provider_id_required' => 'حقل المزود مطلوب',
    'service_id_required_if' => 'مطلوب مجال الخدمات ذات الصلة عندما تكون الخدمات ذات الصلة العشوائية متوقفة.',
    'type' => 'الرجاء تحديد نوع الخدمة',
    'price_required_if' => 'حقل السعر مطلوب',

    'type_in' => 'يمكن أن يكون نوع العلامة إما منشورًا أو منتجًا',
    'rate_regex' => 'حدد معدل الضريبة بين 0 و 99.99.',

    'provider_id_required' => 'حقل المزود مطلوب.',
    'provider_id_exists' => 'المزود المحدد غير صالح.',
    'gap_required' => 'حقل الفجوة مطلوب.',
    'gap_integer' => 'يجب أن تكون الفجوة عددًا صحيحًا.',
    'gap_min' => 'يجب أن تكون الفجوة على الأقل 1.',
    'time_unit_required' => 'مطلوب حقل الوحدة الزمنية.',
    'time_unit_in' => 'الوحدة الزمنية المحددة غير صالحة.',
    'time_slots_required' => 'مطلوب فتحة لمرة واحدة على الأقل.',
    'time_slots_day_required' => 'حقل اليوم مطلوب.',
    'time_slots_day_in' => 'اليوم المحدد غير صالح.',
    'time_slots_start_time_required' => 'حقل وقت البدء مطلوب.',
    'time_slots_start_time_date_format' => 'لا يتطابق وقت البدء مع التنسيق H: i.',
    'time_slots_end_time_required' => 'حقل وقت الانتهاء مطلوب.',
    'time_slots_end_time_date_format' => 'لا يتطابق وقت الانتهاء مع التنسيق H: i.',
    'time_slots_end_time_after' => 'يجب أن يكون وقت الانتهاء بعد وقت البدء.',

    'payment_type_in' => 'يجب أن يكون نوع الدفع PayPal أو بنك.',

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
        'attribute-name' => [
            'rule-name' => 'رسالة مخصصة',
        ],
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
