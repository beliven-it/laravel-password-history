<?php

namespace Beliven\PasswordHistory\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $hash
 *
 * @method static \Illuminate\Database\Eloquent\Builder|User byModel($value)
 */
class PasswordHash extends Model
{
    use HasFactory;

    protected $table = 'password_hashes';

    protected $hidden = [
        'hash',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function scopeByModel(Builder $query, Model $model): Builder
    {
        $id = $model->getAttribute('id');

        return $query
            ->where('model_type', $model::class)
            ->where('model_id', $id);
    }
}
