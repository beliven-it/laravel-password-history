<?php

namespace Beliven\PasswordHistory\Entities\Enums;

enum DomainErrorsEnum
{
    case PASSWORD_IN_HISTORY;
    case PASSWORD_ALREADY_HASHED;

    public function code(): int
    {
        return match ($this) {
            DomainErrorsEnum::PASSWORD_IN_HISTORY     => 400,
            DomainErrorsEnum::PASSWORD_ALREADY_HASHED => 400,
        };
    }

    public function message(): string
    {
        return match ($this) {
            DomainErrorsEnum::PASSWORD_IN_HISTORY     => 'Password already in history',
            DomainErrorsEnum::PASSWORD_ALREADY_HASHED => 'Password already hashed',
        };
    }
}
