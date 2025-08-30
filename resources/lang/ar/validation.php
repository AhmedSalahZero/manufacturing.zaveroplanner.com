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

    'accepted' => 'هذا البيان must be accepted.',
    'active_url' => 'هذا البيان is not a valid URL.',
    'after' => 'هذا البيان must be a date after :date.',
    'after_or_equal' => '.بيان :attribute يجب أن يكون مساوي أو بعد :date',
    'alpha' => 'هذا البيان may only contain letters.',
    'alpha_dash' => 'هذا البيان may only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'هذا البيان may only contain letters and numbers.',
    'array' => 'هذا البيان must be an array.',
    'before' => 'هذا البيان must be a date before :date.',
    'before_or_equal' => 'هذا البيان must be a date before or equal to :date.',
    'between' => [
        'numeric' => 'هذا البيان must be between :min and :max.',
        'file' => 'هذا البيان must be between :min and :max kilobytes.',
        'string' => 'هذا البيان must be between :min and :max characters.',
        'array' => 'هذا البيان must have between :min and :max items.',
    ],
    'boolean' => 'هذا البيان field must be true or false.',
    'confirmed' => ' :attribute ليست متطابقة.',
    'date' => 'هذا البيان is not a valid date.',
    'date_equals' => 'هذا البيان must be a date equal to :date.',
    'date_format' => 'هذا البيان does not match the format :format.',
    'different' => 'هذا البيان and :other must be different.',
    'digits' => 'هذا البيان must be :digits digits.',
    'digits_between' => 'هذا البيان must be between :min and :max digits.',
    'dimensions' => 'هذا البيان has invalid image dimensions.',
    'distinct' => 'هذا البيان field has a duplicate value.',
    'email' => 'من فضلك ادخل التنسيق الصحيح للـ:attribute.',
    'ends_with' => 'هذا البيان must end with one of the following: :values.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'هذا البيان must be a file.',
    'filled' => 'هذا البيان field must have a value.',
    'gt' => [
        'numeric' => 'هذا البيان must be greater than :value.',
        'file' => 'هذا البيان must be greater than :value kilobytes.',
        'string' => 'هذا البيان must be greater than :value characters.',
        'array' => 'هذا البيان must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'هذا البيان must be greater than or equal :value.',
        'file' => 'هذا البيان must be greater than or equal :value kilobytes.',
        'string' => 'هذا البيان must be greater than or equal :value characters.',
        'array' => 'هذا البيان must have :value items or more.',
    ],
    'image' => 'هذا البيان must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'هذا البيان field does not exist in :other.',
    'integer' => 'هذا البيان must be an integer.',
    'ip' => 'هذا البيان must be a valid IP address.',
    'ipv4' => 'هذا البيان must be a valid IPv4 address.',
    'ipv6' => 'هذا البيان must be a valid IPv6 address.',
    'json' => 'هذا البيان must be a valid JSON string.',
    'lt' => [
        'numeric' => 'هذا البيان must be less than :value.',
        'file' => 'هذا البيان must be less than :value kilobytes.',
        'string' => 'هذا البيان must be less than :value characters.',
        'array' => 'هذا البيان must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'هذا البيان must be less than or equal :value.',
        'file' => 'هذا البيان must be less than or equal :value kilobytes.',
        'string' => 'هذا البيان must be less than or equal :value characters.',
        'array' => 'هذا البيان must not have more than :value items.',
    ],
    'max' => [
        'numeric' => 'هذا البيان may not be greater than :max.',
        'file' => '.يجب أن تكون ال:attribute أصغر من :max  كيلو بايت',
        'string' => 'الحد الأقصى لل:attribute يجب أن يكون على الأكثر:max.',
        'array' => 'هذا البيان may not have more than :max items.',
    ],
    'mimes' => 'يجب أن تكون صيغة ال:attribute من النوع: :values.',
    'mimetypes' => 'هذا البيان must be a file of type: :values.',
    'min' => [
        'numeric' => 'الحد الادنى لهذا البيان يجب أن يكون على الأقل :min.',
        'file' => 'هذا البيان must be at least :min kilobytes.',
        'string' => 'الحد الادنى ل:attribute يجب أن يكون على الأقل :min حروف.',
        'array' => 'هذا البيان must have at least :min items.',
    ],
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'هذا البيان format is invalid.',
    'numeric' => 'من فضلك ضع رقم ولا تضع نص.',
    'password' => 'الرقم السري غير صحيح.',
    'present' => 'هذا البيان field must be present.',
    'regex' => 'تنسيق :attribute غير صحيح.',
    'required' => '.من فضلك ادخل هذا البيان',
    'required_if' => 'ال:attribute مطلوب عندما يكون :other :value.',
    'required_unless' => 'هذا البيان field is required unless is :values.',
    'required_with' => 'هذا البيان field is required when :values is present.',
    'required_with_all' => 'هذا البيان field is required when :values are present.',
    'required_without' => 'هذا البيان field is required when :values is not present.',
    'required_without_all' => 'هذا البيان field is required when none of :values are present.',
    'same' => ':attribute و :other غير متشابهين.',
    'size' => [
        'numeric' => 'هذا البيان must be :size.',
        'file' => 'هذا البيان must be :size kilobytes.',
        'string' => 'هذا البيان must be :size characters.',
        'array' => 'هذا البيان must contain :size items.',
    ],
    'starts_with' => 'هذا البيان must start with one of the following: :values.',
    'string' => 'هذا البيان must be a string.',
    'timezone' => 'هذا البيان must be a valid zone.',
    'unique' => 'هذا :attribute موجود بالفعل.',
    'uploaded' => 'هذا البيان failed to upload.',
    'url' => 'هذا البيان format is invalid.',
    'uuid' => 'هذا البيان must be a valid UUID.',

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
            'rule-name' => 'custom-message',

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

    'attributes' => [
        'name' => "الاسم",
        'email' => "البريد الإلكتروني",
        'work_place' => 'مكان العمل' ,
        'password' => 'كلمة السر',
        'date_of_birth' => 'تاريخ الميلاد',
        'gender' => 'النوع' ,
        'last_name' => 'اسم العائلة',
        'month' => 'الشهر',
        'year' => 'السنة',
        'start_date' => 'تاريخ البدء',
        'duration' => 'الفترة',
        'selling_start_month' => 'شهر بدء البيع',
        'selling_start_year' => 'سنه بدء البيع',
        'selling_start_date' => 'تاريخ بداية البيع',
        'country_id' => 'الدولة',
        'state_id' => 'المدينة',
        'city_id' => 'المنطقة',
        'return_rate' => 'نسبة عائد الإستثمار المطلوب',
        'land_area' =>  "مساحة الأرض",
        'land_cost' => "قيمة الأرض",
        'design_drawing_cost' => "الرسومات و التصميمات",
        'offical_permissions' => "تكلفة التصاريح الرسمية",
        'down_payment' => "نسبة الدفعة المقدمة",
        'balance_rate' => "النسبة المتبقية",
        'installment_count' => "عدد الأقساط",
        'balance_rate_interval' => "فترة الأقساط",
        'interest_rate' => "نسبة الفوائد",
        'residential' => "السكنية",
        'commercial' => "التجارية",
        'administrative' => "الإدارية",
        'medical' => "الطبية",

        'builtup_area' => 'المساحة الإنشائية',
        'cost_per_unit' => 'تكلفة وحدة المساحة',
        'annual_progression_rate' => 'نسبة الزيادة السنوية',
        'contingency_rate' => 'نسبة الإحتياط',
        'total_percentage' => 'إجمالي النسبة',
        'installment_collection_interval' =>'فترة الأقساط',
        'installment_collection_rate' => 'إجمالي نسبة الأقساط',
        'annual_collection_rate' => 'إجمالي نسبة الدفعات السنوية' ,

        'selling_price' => 'السعر البيعي',
        'installment_collection_count' => 'عدد الأقساط',
        'sales_commission_payment_interval' => 'فترة دفع العمولات البيعية',
    ],


];
