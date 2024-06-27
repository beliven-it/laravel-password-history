<?php

namespace Beliven\PasswordHistory\Traits;

use Beliven\PasswordHistory\PasswordHistory as PasswordHistoryService;

trait HasPasswordHistory
{
    public function hasPasswordInHistory(string $new_password): bool
    {
        $password_history_service = new PasswordHistoryService();

        return $password_history_service->hasPasswordInHistory($this, $new_password);
    }

    public function addPasswordInHistory(string $new_password): void
    {
        $password_history_service = new PasswordHistoryService();

        $password_history_service->addPasswordToHistory($this, $new_password);
    }
}
