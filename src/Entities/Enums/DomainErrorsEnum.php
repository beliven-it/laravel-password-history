<?php

namespace Beliven\PasswordHistory\Entities\Enums;

enum DomainErrorsEnum: string
{
    case PASSWORD_IN_HISTORY = 'Password already in history';
    case PASSWORD_HAS_HASHED_CAST = 'Password has hashed cast. The library cannot check if the password is in the history with this setting enabled';

    public function code(): int
    {
        return match ($this) {
            DomainErrorsEnum::PASSWORD_IN_HISTORY      => 400,
            DomainErrorsEnum::PASSWORD_HAS_HASHED_CAST => 500,
        };
    }
}
