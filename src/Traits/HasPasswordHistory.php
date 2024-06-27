<?php

namespace Beliven\PasswordHistory\Traits;

use Beliven\PasswordHistory\PasswordHistory as PasswordHistoryService;
use Illuminate\Support\Facades\DB;

trait HasPasswordHistory
{
    protected string $password_field_column = 'password';

    public function hasPasswordInHistory(string $new_password): bool
    {
        $password_history_service = new PasswordHistoryService();

        return $password_history_service->hasPasswordInHistory($this, $new_password);
    }

    public function addPasswordInHistory(string $new_password): void
    {
        DB::transaction(function () use ($new_password) {
            $password_history_service = new PasswordHistoryService();
            $password_entry = $password_history_service->addPasswordToHistory($this, $new_password);

            $this[$this->password_field_column] = $password_entry->hash;
            $this->save();
        });
    }
}
