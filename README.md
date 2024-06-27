# Laravel Password History

[![Latest Version on Packagist](https://img.shields.io/packagist/v/beliven-it/laravel-password-history.svg?style=flat-square)](https://packagist.org/packages/beliven-it/laravel-password-history)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/beliven-it/laravel-password-history/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/beliven-it/laravel-password-history/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/beliven-it/laravel-password-history/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/beliven-it/laravel-password-history/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/beliven-it/laravel-password-history.svg?style=flat-square)](https://packagist.org/packages/beliven-it/laravel-password-history)

This package allows you to keep track of the last passwords used by a user.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-password-history.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-password-history)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require beliven-it/laravel-password-history
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="password-history-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="password-history-config"
```

This is the contents of the published config file:

```php
return [
    // Use -1 for unlimited history
    'depth' => (int) env('PASSWORD_HISTORY_DEPTH', 10),
];
```

The only value configurable is the depth of the password history. This value is the number of passwords that will be stored in the history. When a user tries to set a password that is in the history, the validation will fail.

## Usage

The library expose a trait that you can use in your Own model.

For example suppose to use it in the User model:

```php
<?php

namespace App\Models;

use Beliven\PasswordHistory\Traits\HasPasswordHistory;

class User extends Authenticatable
{
    use HasPasswordHistory;
    // ... other stuff
}
```

When you need to create / update a user password you can invoce the trait method `savePassword` like below:

```php
$user->addPasswordInHistory($password_from_request);
```

You can also check if a password is in the history of the user inside the request:

```php
<?php

namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Beliven\PasswordHistory\Rules\HasPasswordInHistory;

class UpdatePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'password_current' => 'required|current_password:api|max:100',
            'password'         => [
                'required',
                'confirmed',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                new HasPasswordInHistory($this->user()),
            ],
        ];
    }
}
```


## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Fabrizio Gortani](https://github.com/beliven-it)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
