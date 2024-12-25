<?php

namespace Beliven\PasswordHistory\Traits;

use Beliven\PasswordHistory\Facades\PasswordHistory;
use Illuminate\Support\Facades\DB;

trait HasPasswordHistory
{
    protected string $password_field_column = 'password';

    private ?string $plain_text_password = null;

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
            $model->handleCreating();
        });

        static::updating(function ($model) {
            $model->handleUpdating();
        });
    }

    protected function castAttributeAsHashedString($key, $value): string
    {
        $this->plain_text_password = $value;

        return parent::castAttributeAsHashedString($key, $value);
    }

    public function hasPasswordInHistory(string $newPassword): bool
    {
        return PasswordHistory::hasPasswordInHistory($this, $newPassword);
    }

    protected function savePasswordInHistory(string $newPassword): void
    {
        DB::transaction(function () use ($newPassword) {
            $passwordEntry = PasswordHistory::addPasswordToHistory($this, $newPassword);
        });
    }

    protected function handleCreating(): void
    {
        if (is_null($this->plain_text_password)) {
            return;
        }

        $this->savePasswordInHistory($this->plain_text_password);
    }

    protected function handleUpdating(): void
    {
        if (!$this->isDirty($this->password_field_column)) {
            return;
        }

        if (is_null($this->plain_text_password)) {
            return;
        }

        $this->savePasswordInHistory($this->plain_text_password);
    }
}
