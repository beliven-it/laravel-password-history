<?php

namespace Beliven\PasswordHistory\Traits;

use Beliven\PasswordHistory\PasswordHistory as PasswordHistoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait HasPasswordHistory
{
    protected string $password_field_column = 'password';

    private static ?string $plain_text_password = null;

    protected function traitCasts(): array
    {
        return [
            $this->password_field_column => 'hashed',
        ];
    }

    public function getCasts(): array
    {
        return array_merge(parent::getCasts(), $this->traitCasts());
    }

    protected static function bootHasPasswordHistory(): void
    {
        static::created(function ($model) {
            self::handleCreating($model);
        });

        static::updating(function ($model) {
            self::handleUpdating($model);
        });
    }

    protected function castAttributeAsHashedString($key, $value): string
    {
        self::$plain_text_password = $value;

        return parent::castAttributeAsHashedString($key, $value);
    }

    public function hasPasswordInHistory(string $newPassword): bool
    {
        $passwordHistoryService = new PasswordHistoryService;

        return $passwordHistoryService->hasPasswordInHistory($this, $newPassword);
    }

    protected function savePasswordInHistory(string $newPassword): void
    {
        DB::transaction(function () use ($newPassword) {
            $passwordHistoryService = new PasswordHistoryService;
            $passwordEntry = $passwordHistoryService->addPasswordToHistory($this, $newPassword);
        });
    }

    private static function handleCreating(Model $model): void
    {
        if (is_null(self::$plain_text_password)) {
            return;
        }

        $model->savePasswordInHistory(self::$plain_text_password);
    }

    private static function handleUpdating(Model $model): void
    {
        if (!$model->isDirty($model->password_field_column)) {
            return;
        }

        if (is_null(self::$plain_text_password)) {
            return;
        }

        $model->savePasswordInHistory(self::$plain_text_password);
    }
}
