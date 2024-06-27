<?php

namespace Beliven\PasswordHistory\Entities\Enums;

enum DomainErrorsEnum: string
{
    case PASSWORD_IN_HISTORY = 'Password already in history';
    case PASSWORD_HAS_HASHED_CAST = 'Password has hashed cast. The library cannot check if the password is in the history with this setting enabled';
    case MISSING_GET_PASSWORD_FIELD_COLUMN_METHOD = 'The getPasswordFieldColumn method is missing in the model';

    public function code(): int
    {
        return match ($this) {
            DomainErrorsEnum::PASSWORD_IN_HISTORY                      => 400,
            DomainErrorsEnum::PASSWORD_HAS_HASHED_CAST                 => 500,
            DomainErrorsEnum::MISSING_GET_PASSWORD_FIELD_COLUMN_METHOD => 500,
        };
    }
}
