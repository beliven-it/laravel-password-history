<?php

namespace Beliven\PasswordHistory\Exceptions;

use Beliven\PasswordHistory\Entities\Enums\DomainErrorsEnum;

class PasswordHasHashedCastException extends BaseException
{
    public function __construct()
    {
        parent::__construct(DomainErrorsEnum::PASSWORD_HAS_HASHED_CAST);
    }
}
