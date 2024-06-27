<?php

use Beliven\PasswordHistory\Models\PasswordHash;
use Beliven\PasswordHistory\PasswordHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class TestModel extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public $password = 'password';

    protected $table = 'test_models';
}

beforeEach(function () {
    $this->passwordHistory = new PasswordHistory();

    Config::set('password-history.depth', 5);
});

it('should returns false if there are no passwords in history', function () {
    $model = TestModel::create();
    $result = $this->passwordHistory->hasPasswordInHistory($model, 'password');
    expect($result)->toBeFalse();
});

it('should returns true if the password is in history', function () {
    $model = TestModel::create();
    $password_hash = new PasswordHash();
    $password_hash->hash = Hash::make('password');
    $password_hash->model_type = get_class($model);
    $password_hash->model_id = $model->id;
    $password_hash->save();

    $result = $this->passwordHistory->hasPasswordInHistory($model, 'password');
    expect($result)->toBeTrue();
});

it('should adds a new password to history', function () {
    $model = TestModel::create();
    $newPassword = 'new_password';

    $passwordHash = $this->passwordHistory->addPasswordToHistory($model, $newPassword);

    $this->assertDatabaseHas('password_hashes', [
        'hash'       => $passwordHash->hash,
        'model_type' => get_class($model),
        'model_id'   => $model->id,
    ]);
});

it('should throws an exception when adding a password already in history', function () {
    $model = TestModel::create();
    $existingPassword = 'existing_password';

    $password_hash = new PasswordHash();
    $password_hash->hash = Hash::make($existingPassword);
    $password_hash->model_type = get_class($model);
    $password_hash->model_id = $model->id;
    $password_hash->save();

    expect(fn () => $this->passwordHistory->addPasswordToHistory($model, $existingPassword))
        ->toThrow(Exception::class, __('Password already in history'));
});

it('removes the oldest password when history depth is exceeded', function () {
    $model = TestModel::create();
    Config::set('password-history.depth', 2); // Set depth to 2 for testing

    $this->passwordHistory->addPasswordToHistory($model, 'password1');
    $this->passwordHistory->addPasswordToHistory($model, 'password2');
    $this->passwordHistory->addPasswordToHistory($model, 'password3');

    $valid_password_count = 0;

    if ($this->passwordHistory->hasPasswordInHistory($model, 'password1')) {
        $valid_password_count++;
    }

    if ($this->passwordHistory->hasPasswordInHistory($model, 'password2')) {
        $valid_password_count++;
    }

    if ($this->passwordHistory->hasPasswordInHistory($model, 'password3')) {
        $valid_password_count++;
    }

    expect($valid_password_count)->toBe(2);
});
