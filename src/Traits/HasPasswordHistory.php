<?php

namespace Beliven\PasswordHistory\Traits;

use Beliven\PasswordHistory\PasswordHistory as PasswordHistoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait HasPasswordHistory
{
    protected string $password_field_column = 'password';

    private ?string $plain_text_password = null;

    protected static function bootHasPasswordHistory()
    {
        parent::booted();

        static::booted(function ($model) {
            $model->mergeHidden(['plain_text_password']);
        });

        static::saving(function ($model) {
            self::handleSaving($model);
        });
    }

    private static function handleSaving(Model $model)
    {
        if (!$model->isDirty('password')) {
            return;
        }

        $plain_text_password = $model->getPlainTextPassword();

        DB::transaction(function () use ($model, $plain_text_password) {
            $password_history_service = new PasswordHistoryService();
            $password_entry = $password_history_service->addPasswordToHistory($model, $plain_text_password);

            $model[$model->password_field_column] = $password_entry->hash;
        });
    }

    public function getPlainTextPassword(): string
    {
        return $this->plain_text_password;
    }

    public function __set($key, $value)
    {
        if ($key === $this->password_field_column) {
            if (is_null($this->plain_text_password)) {
                $this->plain_text_password = $value;
            }

            $this->attributes[$this->password_field_column] = $value;

            return;
        }

        $this->attributes[$key] = $value;
    }

    public function hasPasswordInHistory(string $new_password): bool
    {
        $password_history_service = new PasswordHistoryService();

        return $password_history_service->hasPasswordInHistory($this, $new_password);
    }
}
