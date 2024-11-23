# Laravel Password History

[![Latest Version on Packagist](https://img.shields.io/packagist/v/beliven-it/laravel-password-history.svg?style=flat-square)](https://packagist.org/packages/beliven-it/laravel-password-history)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/beliven-it/laravel-password-history/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/beliven-it/laravel-password-history/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/beliven-it/laravel-password-history/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/beliven-it/laravel-password-history/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/beliven-it/laravel-password-history.svg?style=flat-square)](https://packagist.org/packages/beliven-it/laravel-password-history)

This package allows you to keep track of the last passwords used by a user.

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

The library allow to apply a trait in your own models.

Let's try to use in the `User` model:

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

Now, when you need to create / update a user password you can use the following procedure:

```php
$user->password = $password_from_request;
$user->save();
```

or instead use the trait method `addPasswordInHistory` like below:

```php
$user->addPasswordInHistory($password_from_request);
```

Both of these methods throws an exception if the password is already in the history. So make sure to catch it.


You can also use a rule in your request validation:

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
