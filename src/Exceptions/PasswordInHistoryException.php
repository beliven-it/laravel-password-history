<?php

namespace Beliven\PasswordHistory\Exceptions;

use Beliven\PasswordHistory\Entities\Enums\DomainErrorsEnum;

class PasswordInHistoryException extends BaseException
{
    public function __construct()
    {
        parent::__construct(DomainErrorsEnum::PASSWORD_IN_HISTORY);
    }
}
