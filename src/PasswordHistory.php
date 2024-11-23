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
        return PasswordHash::query()->byModel($model)->count();
    }

    private function removeModelOldestHash(Model $model): void
    {
        PasswordHash::orderBy('created_at', 'asc')
            ->byModel($model)
            ->first()
            ->delete();
    }

    public function hasPasswordInHistory(Model $model, string $new_password): bool
    {
        $list_of_passwords = PasswordHash::query()
            ->whereHasMorph('model', $model::class)
            ->get();

        foreach ($list_of_passwords as $password) {
            $hash = $password->getAttribute('hash');

            if (Hash::check($new_password, $hash)) {
                return true;
            }
        }

        return false;
    }

    public function addPasswordToHistory(Model $model, string $new_password): ?PasswordHash
    {
        $history_depth = config('password-history.depth');

        if ($this->hasPasswordInHistory($model, $new_password)) {
            throw new PasswordInHistoryException;
        }

        return DB::transaction(function () use ($model, $new_password, $history_depth) {
            $password_instance = new PasswordHash;
            $password_instance->hash = Hash::make($new_password);
            $password_instance->model()->associate($model);
            $password_instance->save();

            $password_history_count = $this->getModelPasswordHistoryCount($model);

            if ($history_depth > 0) {
                if ($password_history_count > $history_depth) {
                    $this->removeModelOldestHash($model);
                }
            }

            return $password_instance;
        });
    }
}
