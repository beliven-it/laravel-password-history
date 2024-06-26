<?php

namespace Beliven\PasswordHistory\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class HasPasswordInHistory implements ValidationRule
{
    public function __construct(private Model $model) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (method_exists($this->model, 'hasPasswordInHistory') && $this->model->hasPasswordInHistory($value)) {
            $fail(__('Password already in history'));
        }
    }
}
