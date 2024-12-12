<?php

namespace Beliven\PasswordHistory\Exceptions;

use Beliven\PasswordHistory\Entities\Enums\DomainErrorsEnum;

class PasswordAlreadyHashedException extends BaseException
{
    public function __construct()
    {
        parent::__construct(DomainErrorsEnum::PASSWORD_ALREADY_HASHED);
    }
}
