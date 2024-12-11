<?php

namespace Beliven\PasswordHistory\Traits;

use Beliven\PasswordHistory\PasswordHistory as PasswordHistoryService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

trait HasPasswordHistory
{
    protected string $password_field_column = 'password';

    private static ?string $plain_text_password = null;

    protected static function bootHasPasswordHistory(): void
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
                $hash = Hash::make($value);

                return $hash;
            }
        );
    }

    public function hasPasswordInHistory(string $newPassword): bool
    {
        $passwordHistoryService = new PasswordHistoryService;

        return $passwordHistoryService->hasPasswordInHistory($this, $newPassword);
    }

    public function addPasswordInHistory(string $newPassword): void
    {
        $this->savePasswordInHistory($newPassword);
    }

    protected function savePasswordInHistory(string $newPassword, bool $explicit = true): void
    {
        DB::transaction(function () use ($newPassword, $explicit) {
            $passwordHistoryService = new PasswordHistoryService;
            $passwordEntry = $passwordHistoryService->addPasswordToHistory($this, $newPassword);

            if ($explicit) {
                $this[$this->password_field_column] = $passwordEntry->hash;
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
