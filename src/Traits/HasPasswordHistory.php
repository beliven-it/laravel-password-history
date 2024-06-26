<?php

namespace Beliven\PasswordHistory\Traits;

use Beliven\PasswordHistory\Models\PasswordHash;
use Beliven\PasswordHistory\PasswordHistory as PasswordHistoryService;

trait HasPasswordHistory
{
    public function hasPasswordInHistory(string $new_password): bool
    {
        $password_history_service = new PasswordHistoryService();

        return $password_history_service->hasPasswordInHistory($this, $new_password);
    }

    public function addPasswordToHistory(string $new_password): ?PasswordHash
    {
        $password_history_service = new PasswordHistoryService();

        return $password_history_service->addPasswordToHistory($this, $new_password);
    }

    public function savePassword(string $password)
    {
        $new_password = $this->addPasswordToHistory($password);
        $this->password = $new_password->hash;
        $this->save();
    }
}
