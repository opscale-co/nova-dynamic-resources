<?php

return [
    'fields' => [
        'address' => [
            'field' => \Laravel\Nova\Fields\Place::class,
            'rules' => ['nullable', 'string', 'max:500'],
            'config' => [
                'searchLabel' => 'Enter address...',
                'withoutCardStyles' => true,
            ],
        ],

        'audio' => [
            'field' => \Laravel\Nova\Fields\Audio::class,
            'rules' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:20480'],
            'config' => [
                'acceptedTypes' => 'audio/*',
                'prunable' => true,
            ],
        ],

        'city' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'rules' => ['nullable', 'string', 'max:100'],
            'config' => [
                'placeholder' => 'Enter city name',
                'suggestions' => true,
            ],
        ],

        'color' => [
            'field' => \Laravel\Nova\Fields\Color::class,
            'rules' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'config' => [
                'sketch' => true,
            ],
        ],

        'country' => [
            'field' => \Laravel\Nova\Fields\Country::class,
            'rules' => ['nullable', 'string', 'size:2'],
            'config' => [
                'searchable' => true,
            ],
        ],

        'date' => [
            'field' => \Laravel\Nova\Fields\Date::class,
            'rules' => ['nullable', 'date'],
            'config' => [
                'pickerDisplayFormat' => 'Y-m-d',
                'firstDayOfWeek' => 1,
            ],
        ],

        'description' => [
            'field' => \Laravel\Nova\Fields\Textarea::class,
            'rules' => ['nullable', 'string', 'max:5000'],
            'config' => [
                'rows' => 5,
                'withoutCardStyles' => true,
            ],
        ],

        'document' => [
            'field' => \Laravel\Nova\Fields\File::class,
            'rules' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx', 'max:10240'],
            'config' => [
                'acceptedTypes' => '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx',
                'prunable' => true,
            ],
        ],

        'email' => [
            'field' => \Laravel\Nova\Fields\Email::class,
            'rules' => ['nullable', 'email:rfc,dns', 'max:255'],
            'config' => [
                'withMeta' => ['extraAttributes' => ['autocomplete' => 'email']],
            ],
        ],

        'file' => [
            'field' => \Laravel\Nova\Fields\File::class,
            'rules' => ['nullable', 'file', 'max:10240'],
            'config' => [
                'prunable' => true,
                'deletable' => true,
            ],
        ],

        'gender' => [
            'field' => \Laravel\Nova\Fields\Select::class,
            'rules' => ['nullable', 'string', 'in:male,female,other,prefer_not_to_say'],
            'config' => [
                'options' => [
                    'male' => 'Male',
                    'female' => 'Female',
                    'other' => 'Other',
                    'prefer_not_to_say' => 'Prefer not to say',
                ],
                'displayUsingLabels' => true,
            ],
        ],

        'hash' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'rules' => ['nullable', 'string', 'regex:/^[a-fA-F0-9]+$/'],
            'config' => [
                'readonly' => true,
                'copyable' => true,
            ],
        ],

        'image' => [
            'field' => \Laravel\Nova\Fields\Image::class,
            'rules' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
            'config' => [
                'acceptedTypes' => 'image/*',
                'prunable' => true,
                'preview' => true,
            ],
        ],

        'ip' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'rules' => ['nullable', 'ip'],
            'config' => [
                'placeholder' => '192.168.1.1',
                'copyable' => true,
            ],
        ],

        'language' => [
            'field' => \Laravel\Nova\Fields\Select::class,
            'rules' => ['nullable', 'string', 'max:10'],
            'config' => [
                'options' => [
                    'en' => 'English',
                    'es' => 'Spanish',
                    'fr' => 'French',
                    'de' => 'German',
                    'it' => 'Italian',
                    'pt' => 'Portuguese',
                    'ru' => 'Russian',
                    'zh' => 'Chinese',
                    'ja' => 'Japanese',
                    'ko' => 'Korean',
                ],
                'searchable' => true,
                'displayUsingLabels' => true,
            ],
        ],

        'location' => [
            'field' => \Laravel\Nova\Fields\Place::class,
            'rules' => ['nullable', 'string', 'max:500'],
            'config' => [
                'countries' => ['US', 'CA', 'GB', 'AU'],
                'showLatitude' => true,
                'showLongitude' => true,
            ],
        ],

        'maritalStatus' => [
            'field' => \Laravel\Nova\Fields\Select::class,
            'rules' => ['nullable', 'string', 'in:single,married,divorced,widowed,separated'],
            'config' => [
                'options' => [
                    'single' => 'Single',
                    'married' => 'Married',
                    'divorced' => 'Divorced',
                    'widowed' => 'Widowed',
                    'separated' => 'Separated',
                ],
                'displayUsingLabels' => true,
            ],
        ],

        'moment' => [
            'field' => \Laravel\Nova\Fields\DateTime::class,
            'rules' => ['nullable', 'date'],
            'config' => [
                'pickerDisplayFormat' => 'Y-m-d H:i:s',
                'pickerFormat' => 'Y-m-d H:i:S',
            ],
        ],

        'money' => [
            'field' => \Laravel\Nova\Fields\Currency::class,
            'rules' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'config' => [
                'currency' => 'USD',
                'locale' => 'en-US',
            ],
        ],

        'name' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'rules' => ['nullable', 'string', 'max:255'],
            'config' => [
                'placeholder' => 'Enter full name',
                'suggestions' => true,
            ],
        ],

        'password' => [
            'field' => \Laravel\Nova\Fields\Password::class,
            'rules' => ['nullable', 'string', 'min:8', 'confirmed'],
            'config' => [
                'creationRules' => 'required|string|min:8|confirmed',
                'updateRules' => 'nullable|string|min:8|confirmed',
            ],
        ],

        'pdf' => [
            'field' => \Laravel\Nova\Fields\File::class,
            'rules' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'config' => [
                'acceptedTypes' => '.pdf',
                'prunable' => true,
            ],
        ],

        'phone' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'rules' => ['nullable', 'string', 'regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/'],
            'config' => [
                'placeholder' => '+1 (555) 123-4567',
                'withMeta' => ['extraAttributes' => ['type' => 'tel']],
            ],
        ],

        'post' => [
            'field' => \Laravel\Nova\Fields\Trix::class,
            'rules' => ['nullable', 'string'],
            'config' => [
                'withFiles' => true,
            ],
        ],

        'postalCode' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'rules' => ['nullable', 'string', 'regex:/^[A-Za-z0-9\s\-]+$/'],
            'config' => [
                'placeholder' => '12345 or A1B 2C3',
                'withMeta' => ['extraAttributes' => ['autocomplete' => 'postal-code']],
            ],
        ],

        'quantity' => [
            'field' => \Laravel\Nova\Fields\Number::class,
            'rules' => ['nullable', 'integer', 'min:0'],
            'config' => [
                'min' => 0,
                'step' => 1,
            ],
        ],

        'rating' => [
            'field' => \Laravel\Nova\Fields\Number::class,
            'rules' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'config' => [
                'min' => 0,
                'max' => 5,
                'step' => 0.5,
            ],
        ],

        'region' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'rules' => ['nullable', 'string', 'max:100'],
            'config' => [
                'placeholder' => 'Enter region',
            ],
        ],

        'slug' => [
            'field' => \Laravel\Nova\Fields\Slug::class,
            'rules' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'config' => [
                'from' => 'title',
                'separator' => '-',
            ],
        ],

        'snippet' => [
            'field' => \Laravel\Nova\Fields\Code::class,
            'rules' => ['nullable', 'string'],
            'config' => [
                'language' => 'php',
                'theme' => 'material',
            ],
        ],

        'state' => [
            'field' => \Laravel\Nova\Fields\Select::class,
            'rules' => ['nullable', 'string', 'max:100'],
            'config' => [
                'searchable' => true,
                'options' => [], // To be populated dynamically based on country
            ],
        ],

        'title' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'rules' => ['nullable', 'string', 'max:255'],
            'config' => [
                'placeholder' => 'Enter title',
                'sortable' => true,
            ],
        ],

        'token' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'rules' => ['nullable', 'string', 'min:32', 'max:255'],
            'config' => [
                'readonly' => true,
                'copyable' => true,
                'canSee' => function ($request) {
                    return $request->user()->can('view-tokens');
                },
            ],
        ],

        'ulid' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'rules' => ['nullable', 'string', 'size:26', 'regex:/^[0-7][0-9A-HJKMNP-TV-Z]{25}$/'],
            'config' => [
                'readonly' => true,
                'copyable' => true,
            ],
        ],

        'url' => [
            'field' => \Laravel\Nova\Fields\URL::class,
            'rules' => ['nullable', 'url', 'max:2048'],
            'config' => [
                'displayUsing' => function ($value) {
                    return parse_url($value, PHP_URL_HOST) ?? $value;
                },
                'clickable' => true,
            ],
        ],

        'username' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'rules' => ['nullable', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_]+$/'],
            'config' => [
                'placeholder' => 'Enter username',
                'withMeta' => ['extraAttributes' => ['autocomplete' => 'username']],
            ],
        ],

        'uuid' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'rules' => ['nullable', 'uuid'],
            'config' => [
                'readonly' => true,
                'copyable' => true,
            ],
        ],

        'video' => [
            'field' => \Laravel\Nova\Fields\File::class,
            'rules' => ['nullable', 'file', 'mimes:mp4,avi,mov,wmv,webm', 'max:102400'],
            'config' => [
                'acceptedTypes' => 'video/*',
                'prunable' => true,
                'previewUsing' => function ($value, $disk) {
                    return $value ? \Storage::disk($disk)->url($value) : null;
                },
            ],
        ],
    ],
];
