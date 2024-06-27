<?php

namespace Beliven\PasswordHistory\Exceptions;

use Beliven\PasswordHistory\Entities\Enums\DomainErrorsEnum;
use Exception;
use Illuminate\Http\Request;

class BaseException extends Exception
{
    public function __construct(DomainErrorsEnum $error, string $message = '')
    {
        $value = $error->value;
        if (!empty($message)) {
            $value = $message;
        }

        parent::__construct($value, $error->code() ?? 500);
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  Request  $request
     */
    public function render($request): void
    {
        abort($this->getCode(), $this->getMessage());
    }
}
