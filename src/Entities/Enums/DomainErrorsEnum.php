<?php

namespace Beliven\PasswordHistory\Entities\Enums;

enum DomainErrorsEnum: string
{
    case PASSWORD_IN_HISTORY = 'Password already in history';
    case PASSWORD_ALREADY_HASHED = 'Password already hashed';

    public function code(): int
    {
        return match ($this) {
            DomainErrorsEnum::PASSWORD_IN_HISTORY     => 400,
            DomainErrorsEnum::PASSWORD_ALREADY_HASHED => 400,
        };
    }
}
