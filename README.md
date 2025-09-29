## Support us

At Opscale, we’re passionate about contributing to the open-source community by providing solutions that help businesses scale efficiently. If you’ve found our tools helpful, here are a few ways you can show your support:

⭐ **Star this repository** to help others discover our work and be part of our growing community. Every star makes a difference!

💬 **Share your experience** by leaving a review on [Trustpilot](https://www.trustpilot.com/review/opscale.co) or sharing your thoughts on social media. Your feedback helps us improve and grow!

📧 **Send us feedback** on what we can improve at [feedback@opscale.co](mailto:feedback@opscale.co). We value your input to make our tools even better for everyone.

🙏 **Get involved** by actively contributing to our open-source repositories. Your participation benefits the entire community and helps push the boundaries of what’s possible.

💼 **Hire us** if you need custom dashboards, admin panels, internal tools or MVPs tailored to your business. With our expertise, we can help you systematize operations or enhance your existing product. Contact us at hire@opscale.co to discuss your project needs.

Thanks for helping Opscale continue to scale! 🚀



## Description

Create Nova resources UI (tables and forms) based on dynamic configuration stored in the database.

> [!IMPORTANT]  
> This package is experimental and it's not inteded to be used in production (yet)

TODO: Screenshots

## Installation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/opscale-co/nova-dynamic-resources.svg?style=flat-square)](https://packagist.org/packages/opscale-co/nova-dynamic-resources)

You can install the package in to a Laravel app that uses [Nova](https://nova.laravel.com) via composer:

```bash

composer require opscale-co/nova-dynamic-resources

```

Next up, you must register the tool with Nova. This is typically done in the `tools` method of the `NovaServiceProvider`.

```php

// in app/Providers/NovaServiceProvider.php
// ...
public function tools()
{
    return [
        // ...
        new \Opscale\NovaDynamicResources\Tool(),
    ];
}

```

## Usage

### Creating Dynamic Resources

1. Navigate to the "Dynamic Resources" menu item in your Nova app
2. Create a new Dynamic Resource by defining:
   - **Label**: The display name for your resource
   - **Fields**: An array of field definitions using the repeater interface

### Field Configuration

Each field in your dynamic resource consists of:

- **Label**: The display name for the field
- **Name**: The database column name
- **Type**: The business field type (see available types below)
- **Required**: Whether the field is mandatory
- **Validation Rules**: Additional Laravel validation rules
- **Config**: Field-specific configuration options

### Available Field Types

Field types are based on business logic and can be found in the `config/nova-dynamic-resources.php` configuration file. Each type maps to a specific Nova field class with predefined behavior.

You can add more field types by extending the configuration file. The available types include common business field patterns like text, email, phone, address, etc.

### Field Configuration Options

The **Config** section allows you to call specific methods on the Nova field instance. For example:
- `required => true` is equivalent to calling `->required(true)` on the field
- `placeholder => "Enter your name"` calls `->placeholder("Enter your name")`
- `help => "This field is important"` calls `->help("This field is important")`

This approach provides flexibility to configure Nova fields dynamically while maintaining type safety and IDE support.

## Testing

``` bash

npm run test

```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/opscale-co/.github/blob/main/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email development@opscale.co instead of using the issue tracker.

## Credits

- [Opscale](https://github.com/opscale-co)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.