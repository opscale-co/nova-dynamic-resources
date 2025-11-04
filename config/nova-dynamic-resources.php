<?php

return [
    'fields' => [
        'address' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'hint' => 'Physical or mailing address for contact, shipping, or billing purposes',
            'rules' => ['nullable', 'string', 'max:500'],
        ],

        'audio' => [
            'field' => \Laravel\Nova\Fields\Audio::class,
            'hint' => 'Audio files for podcasts, voice recordings, or sound assets',
            'rules' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:20480'],
            'config' => [
                'acceptedTypes' => 'audio/*',
                'prunable' => true,
            ],
        ],

        'city' => [
            'field' => \Laravel\Nova\Fields\Select::class,
            'hint' => 'City name for location-based data and geographic segmentation',
            'rules' => ['nullable', 'string', 'max:100'],
            'config' => [
                'options' => 'options',
                'displayUsingLabels' => true,
                'searchable' => true,
            ],
            'hooks' => [
                'options' => \Opscale\NovaDynamicResources\Services\Actions\SelectOptions::class,
            ]
        ],

        'color' => [
            'field' => \Laravel\Nova\Fields\Color::class,
            'hint' => 'Color values for branding, UI customization, or product attributes',
            'rules' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ],

        'country' => [
            'field' => \Laravel\Nova\Fields\Country::class,
            'hint' => 'Country selection for international operations and regional compliance',
            'rules' => ['nullable', 'string', 'size:2'],
            'config' => [
                'searchable' => true,
            ],
        ],

        'date' => [
            'field' => \Laravel\Nova\Fields\Date::class,
            'hint' => 'Important dates such as deadlines, events, or milestones',
            'rules' => ['nullable', 'date'],
            'cast' => 'date',
        ],

        'description' => [
            'field' => \Laravel\Nova\Fields\Textarea::class,
            'hint' => 'Detailed descriptions, notes, or additional information about items',
            'rules' => ['nullable', 'string', 'max:5000'],
            'config' => [
                'rows' => 5,
                'withoutCardStyles' => true,
            ],
        ],

        'document' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'hint' => 'Government-issued or official document identifiers (National ID, Tax ID, Passport)',
            'rules' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9.\-\/\s]+$/'],
        ],

        'email' => [
            'field' => \Laravel\Nova\Fields\Email::class,
            'hint' => 'Email addresses for communication, notifications, and account management',
            'rules' => ['nullable', 'email:rfc,dns', 'max:255'],
        ],

        'file' => [
            'field' => \Laravel\Nova\Fields\File::class,
            'hint' => 'General file uploads for documents, attachments, or data imports',
            'rules' => ['nullable', 'file', 'max:10240'],
            'config' => [
                'prunable' => true,
                'deletable' => true,
            ],
        ],

        'gender' => [
            'field' => \Laravel\Nova\Fields\Select::class,
            'hint' => 'Gender identification for demographic analysis and personalization',
            'rules' => ['nullable', 'string'],
            'config' => [
                'options' => 'options',
                'displayUsingLabels' => true,
            ],
            'hooks' => [
                'options' => \Opscale\NovaDynamicResources\Services\Actions\SelectOptions::class,
            ]
        ],

        'options' => [
            'field' => \Laravel\Nova\Fields\Select::class,
            'hint' => 'Configurable dropdown options for custom selections and categorization',
            'rules' => ['nullable', 'string'],
            'config' => [
                'options' => 'options',
                'displayUsingLabels' => true,
                'searchable' => true,
            ],
            'hooks' => [
                'options' => \Opscale\NovaDynamicResources\Services\Actions\SelectOptions::class,
            ]
        ],

        'hash' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'hint' => 'Cryptographic hash values for verification, security, or reference IDs',
            'rules' => ['nullable', 'string', 'regex:/^[a-fA-F0-9]+$/'],
            'config' => [
                'readonly' => true,
                'copyable' => true,
            ],
        ],

        'image' => [
            'field' => \Laravel\Nova\Fields\Image::class,
            'hint' => 'Images for product photos, avatars, logos, or visual content',
            'rules' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
            'config' => [
                'acceptedTypes' => 'image/*',
            ],
        ],

        'ip' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'hint' => 'IP addresses for network configuration, security, or access logs',
            'rules' => ['nullable', 'ip'],
            'config' => [
                'copyable' => true,
            ],
        ],

        'language' => [
            'field' => \Laravel\Nova\Fields\Select::class,
            'hint' => 'Preferred language for localization and multilingual content delivery',
            'rules' => ['nullable', 'string', 'max:10'],
            'config' => [
                'options' => 'options',
                'displayUsingLabels' => true,
            ],
            'hooks' => [
                'options' => \Opscale\NovaDynamicResources\Services\Actions\SelectOptions::class,
            ]
        ],

        /*'location' => [
            'field' => \Laravel\Nova\Fields\Place::class,
            'hint' => 'Geographic coordinates for mapping, location services, and spatial analysis',
            'rules' => ['nullable', 'string', 'max:500'],
            'config' => [
                'countries' => ['US', 'CA', 'GB', 'AU'],
                'showLatitude' => true,
                'showLongitude' => true,
            ],
        ],*/

        'maritalStatus' => [
            'field' => \Laravel\Nova\Fields\Select::class,
            'hint' => 'Marital status for demographic profiling and benefit eligibility',
            'rules' => ['nullable', 'string'],
            'config' => [
                'options' => 'options',
                'displayUsingLabels' => true,
            ],
            'hooks' => [
                'options' => \Opscale\NovaDynamicResources\Services\Actions\SelectOptions::class,
            ]
        ],

        'moment' => [
            'field' => \Laravel\Nova\Fields\DateTime::class,
            'hint' => 'Specific timestamps for scheduling, logging, or time-sensitive events',
            'rules' => ['nullable', 'date'],
            'cast' => 'datetime',
            'config' => [
                'pickerDisplayFormat' => 'Y-m-d H:i:s',
                'pickerFormat' => 'Y-m-d H:i:S',
            ],
        ],

        'money' => [
            'field' => \Laravel\Nova\Fields\Currency::class,
            'hint' => 'Monetary amounts for pricing, payments, budgets, or financial transactions',
            'rules' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'cast' => 'decimal:2',
            'config' => [
                'currency' => 'USD',
                'locale' => 'en-US',
            ],
        ],

        'name' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'hint' => 'Full names for identification, personalization, and customer records',
            'rules' => ['nullable', 'string', 'max:255'],
        ],

        'password' => [
            'field' => \Laravel\Nova\Fields\Password::class,
            'hint' => 'Secure passwords for authentication and account protection',
            'rules' => ['nullable', 'string', 'min:8', 'confirmed'],
            'config' => [
                'creationRules' => 'required|string|min:8|confirmed',
                'updateRules' => 'nullable|string|min:8|confirmed',
            ],
        ],

        'pdf' => [
            'field' => \Laravel\Nova\Fields\File::class,
            'hint' => 'PDF documents for reports, contracts, invoices, or official records',
            'rules' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'config' => [
                'acceptedTypes' => '.pdf',
                'prunable' => true,
            ],
        ],

        'phone' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'hint' => 'Phone numbers for contact, verification, and customer support',
            'rules' => ['nullable', 'string', 'regex:/^[\d-]+$/'],
        ],

        'post' => [
            'field' => \Laravel\Nova\Fields\Trix::class,
            'hint' => 'Rich text content for blog posts, articles, or formatted messages',
            'rules' => ['nullable', 'string'],
            'config' => [
                'withFiles' => true,
            ],
        ],

        'postalCode' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'hint' => 'Postal or ZIP codes for shipping, delivery zones, and address validation',
            'rules' => ['nullable', 'string', 'regex:/^[A-Za-z0-9\s\-]+$/'],
            'config' => null,
        ],

        'quantity' => [
            'field' => \Laravel\Nova\Fields\Number::class,
            'hint' => 'Inventory quantities, item counts, or numeric amounts for stock management',
            'rules' => ['nullable', 'integer', 'min:0'],
            'cast' => 'integer',
            'config' => [
                'min' => 0,
                'step' => 1,
            ],
        ],

        'rating' => [
            'field' => \Laravel\Nova\Fields\Number::class,
            'hint' => 'Star ratings or scores for reviews, quality assessment, and feedback',
            'rules' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'cast' => 'float',
            'config' => [
                'min' => 0,
                'max' => 5,
                'step' => 0.5,
            ],
        ],

        'region' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'hint' => 'Geographic regions or territories for sales, distribution, or market analysis',
            'rules' => ['nullable', 'string', 'max:100'],
        ],

        'slug' => [
            'field' => \Laravel\Nova\Fields\Slug::class,
            'hint' => 'URL-friendly identifiers for SEO, routing, and unique page addresses',
            'rules' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'config' => [
                'from' => 'title',
                'separator' => '-',
            ],
        ],

        'snippet' => [
            'field' => \Laravel\Nova\Fields\Code::class,
            'hint' => 'Code snippets, scripts, or technical content with syntax highlighting',
            'rules' => ['nullable', 'string'],
            'config' => [
                'language' => 'php',
                'theme' => 'material',
            ],
        ],

        'state' => [
            'field' => \Laravel\Nova\Fields\Select::class,
            'hint' => 'State or province selection for regional data and administrative divisions',
            'rules' => ['nullable', 'string', 'max:100'],
            'config' => [
                'options' => 'options',
                'displayUsingLabels' => true,
                'searchable' => true,
            ],
            'hooks' => [
                'options' => \Opscale\NovaDynamicResources\Services\Actions\SelectOptions::class,
            ]
        ],

        'title' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'hint' => 'Titles or headings for content, pages, products, or organizational hierarchy',
            'rules' => ['nullable', 'string', 'max:255'],
        ],

        'token' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'hint' => 'API tokens, access keys, or security credentials for authentication',
            'rules' => ['nullable', 'string', 'min:32', 'max:255'],
            'config' => [
                'readonly' => true,
                'copyable' => true,
            ],
        ],

        'ulid' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'hint' => 'Universally Unique Lexicographically Sortable Identifiers for database records',
            'rules' => ['nullable', 'string', 'size:26', 'regex:/^[0-7][0-9A-HJKMNP-TV-Z]{25}$/'],
            'config' => [
                'readonly' => true,
                'copyable' => true,
            ],
        ],

        'url' => [
            'field' => \Laravel\Nova\Fields\URL::class,
            'hint' => 'Website URLs, links, or web references for external resources',
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
            'hint' => 'Unique usernames for login credentials and user identification',
            'rules' => ['nullable', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_]+$/'],
            'config' => null,
        ],

        'uuid' => [
            'field' => \Laravel\Nova\Fields\Text::class,
            'hint' => 'Universally Unique Identifiers for distributed systems and data synchronization',
            'rules' => ['nullable', 'uuid'],
            'config' => [
                'copyable' => true,
            ],
        ],

        'video' => [
            'field' => \Laravel\Nova\Fields\File::class,
            'hint' => 'Video files for tutorials, marketing content, or media libraries',
            'rules' => ['nullable', 'file', 'mimes:mp4,avi,mov,wmv,webm', 'max:102400'],
            'config' => [
                'acceptedTypes' => 'video/*',
                'prunable' => true,
            ],
        ],

        'yesNo' => [
            'field' => \Laravel\Nova\Fields\Boolean::class,
            'hint' => 'Binary choices for feature toggles, permissions, or status indicators',
            'rules' => ['nullable', 'boolean'],
            'cast' => 'boolean',
            'config' => [
                'trueValue' => true,
                'falseValue' => false,
            ],
        ],
    ],
];
