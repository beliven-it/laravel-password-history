<?php

namespace Beliven\PasswordHistory\Exceptions;

use Beliven\PasswordHistory\Entities\Enums\DomainErrorsEnum;

class MissingGetPasswordFieldColumnMethodException extends BaseException
{
    public function __construct()
    {
        parent::__construct(DomainErrorsEnum::MISSING_GET_PASSWORD_FIELD_COLUMN_METHOD);
    }
}
