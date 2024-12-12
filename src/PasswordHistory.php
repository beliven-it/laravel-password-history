<?php

namespace Beliven\PasswordHistory;

use Beliven\PasswordHistory\Exceptions\PasswordInHistoryException;
use Beliven\PasswordHistory\Models\PasswordHash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordHistory
{
    private function getModelPasswordHistoryCount(Model $model): int
    {
        return PasswordHash::byModel($model)->count();
    }

    private function removeModelOldestHash(Model $model): void
    {
        PasswordHash::byModel($model)
            ->orderBy('created_at', 'asc')
            ->first()
            ->delete();
    }

    public function hasPasswordInHistory(Model $model, string $newPassword): bool
    {
        $listOfPasswords = PasswordHash::byModel($model)->get();

        foreach ($listOfPasswords as $password) {
            $hash = $password->getAttribute('hash');

            if (Hash::check($newPassword, $hash)) {
                return true;
            }
        }

        return false;
    }

    public function addPasswordToHistory(Model $model, string $newPassword): ?PasswordHash
    {
        $historyDepth = config('password-history.depth');

        if ($this->hasPasswordInHistory($model, $newPassword)) {
            throw new PasswordInHistoryException;
        }

        return DB::transaction(function () use ($model, $newPassword, $historyDepth) {
            $password_instance = new PasswordHash;
            $password_instance->hash = Hash::make($newPassword);
            $password_instance->model()->associate($model);
            $password_instance->save();

            $passwordHistoryCount = $this->getModelPasswordHistoryCount($model);

            if ($historyDepth > 0) {
                if ($passwordHistoryCount > $historyDepth) {
                    $this->removeModelOldestHash($model);
                }
            }

            return $password_instance;
        });
    }
}
