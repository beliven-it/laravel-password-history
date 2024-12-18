<?php

namespace Beliven\PasswordHistory\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Beliven\PasswordHistory\PasswordHistory
 */
class PasswordHistory extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-password-history';
    }
}
