<?php

namespace Beliven\PasswordHistory\Traits;

use Beliven\PasswordHistory\PasswordHistory as PasswordHistoryService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait HasPasswordHistory
{
    protected string $password_field_column = 'password';

    private static ?string $plain_text_password = null;

    protected static function bootHasPasswordHistory()
    {
        static::saving(function ($model) {
            if (is_null($model->id)) {
                self::handleCreating($model);

                return;
            }
            self::handleUpdating($model);
        });
    }

    public function password(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                self::$plain_text_password = $value;

                return $value;
            }
        );
    }

    public function hasPasswordInHistory(string $new_password): bool
    {
        $password_history_service = new PasswordHistoryService();

        return $password_history_service->hasPasswordInHistory($this, $new_password);
    }

    public function addPasswordInHistory(string $new_password): void
    {
        $this->savePasswordInHistory($new_password);
    }

    protected function savePasswordInHistory(string $new_password, bool $explicit = true): void
    {
        DB::transaction(function () use ($new_password, $explicit) {
            $password_history_service = new PasswordHistoryService();
            $password_entry = $password_history_service->addPasswordToHistory($this, $new_password);

            $this[$this->password_field_column] = $password_entry->hash;

            if ($explicit) {
                $this->save();
            }
        });
    }

    private static function handleCreating(Model $model): void
    {
        $model->savePasswordInHistory(self::$plain_text_password, false);
    }

    private static function handleUpdating(Model $model): void
    {
        if (!$model->isDirty($model->password_field_column)) {
            return;
        }

        $model->savePasswordInHistory(self::$plain_text_password, false);
    }
}
