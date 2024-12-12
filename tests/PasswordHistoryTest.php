<?php

use Beliven\PasswordHistory\Exceptions\PasswordAlreadyHashedException;
use Beliven\PasswordHistory\Exceptions\PasswordInHistoryException;
use Beliven\PasswordHistory\Models\PasswordHash;
use Beliven\PasswordHistory\PasswordHistory;
use Beliven\PasswordHistory\Rules\HasPasswordInHistory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->passwordHistory = new PasswordHistory;

    Config::set('password-history.depth', 5);
});

describe('Password history methods', function () {
    it('should not found password not yet used', function () {
        $model = TestModel::create();
        $result = $this->passwordHistory->hasPasswordInHistory($model, 'password');
        expect($result)->toBeFalse();
    });

    it('should found password already used', function () {
        $model = TestModel::create();
        $passwordHash = new PasswordHash;
        $passwordHash->hash = Hash::make('password');
        $passwordHash->model_type = get_class($model);
        $passwordHash->model_id = $model->id;
        $passwordHash->save();

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

        $match = Hash::check('new_password', $passwordHash->hash);
        expect($match)->toBeTrue();
    });

    it('should prevent the creation of a password already used', function () {
        $model = TestModel::create();
        $existingPassword = 'existing_password';

        $passwordHash = new PasswordHash;
        $passwordHash->hash = Hash::make($existingPassword);
        $passwordHash->model_type = get_class($model);
        $passwordHash->model_id = $model->id;
        $passwordHash->save();

        $this->passwordHistory->addPasswordToHistory($model, $existingPassword);
    })->throws(PasswordInHistoryException::class);

    it('removes the oldest password when history depth is exceeded', function () {
        $model = TestModel::create();
        Config::set('password-history.depth', 2); // Set depth to 2 for testing

        $this->passwordHistory->addPasswordToHistory($model, 'password1');
        $this->passwordHistory->addPasswordToHistory($model, 'password2');
        $this->passwordHistory->addPasswordToHistory($model, 'password3');

        $count = 0;

        if ($this->passwordHistory->hasPasswordInHistory($model, 'password1')) {
            $count++;
        }

        if ($this->passwordHistory->hasPasswordInHistory($model, 'password2')) {
            $count++;
        }

        if ($this->passwordHistory->hasPasswordInHistory($model, 'password3')) {
            $count++;
        }

        expect($count)->toBe(2);
    });
});

describe('Password history via mutator', function () {
    it('should create a password entry', function () {
        $model = new TestModelWithTrait;
        $model->password = 'password';
        $model->id = 123;
        $model->save();

        $this->assertDatabaseHas('password_hashes', [
            'model_type' => get_class($model),
            'model_id'   => $model->id,
        ]);

        expect($model->password)->not()->toBe('password');

        $passwordHash = PasswordHash::byModel($model)->first();
        $match = Hash::check('password', $passwordHash->hash);
        expect($match)->toBeTrue();

        $match = Hash::check('password', $model->password);
        expect($match)->toBeTrue();
    });

    it('should update a user password', function () {
        $model = new TestModelWithTrait;
        $model->password = 'password';
        $model->id = 123;
        $model->save();

        $passwordHash = PasswordHash::byModel($model)->orderBy('id', 'desc')->first();
        $match = Hash::check('password', $passwordHash->hash);
        expect($match)->toBeTrue();

        $model->password = 'password1';
        $model->save();

        $passwordHash = PasswordHash::byModel($model)->orderBy('id', 'desc')->first();
        $match = Hash::check('password1', $passwordHash->hash);
        expect($match)->toBeTrue();

        $match = Hash::check('password1', $model->password);
        expect($match)->toBeTrue();
    });

    it('should not create already used entry', function () {
        $model = new TestModelWithTrait;
        $model->id = 123;

        $model->password = 'password';
        $model->save();

        $model->password = 'password1';
        $model->save();

        $model->password = 'password';
        $model->save();
    })->throws(PasswordInHistoryException::class);

    it('shoud not create a password history entry using quietly methods', function () {
        $model = new TestModelWithTrait;
        $model->id = 123;
        $model->password = 'password';
        $model->saveQuietly();

        $this->assertDatabaseMissing('password_hashes', [
            'model_type' => get_class($model),
            'model_id'   => $model->id,
        ]);
    });
});

describe('Password history edge cases', function () {
    it('should not create an entry using the save quietly method', function () {
        $model = new TestModelWithTrait;
        $model->id = 123;
        $model->password = 'password';
        $model->saveQuietly();

        $match = Hash::check('password', $model->password);
        expect($match)->toBeTrue();

        $count = PasswordHash::byModel($model)->count();
        expect($count)->toBe(0);
    });

    it('should prevent the creation of a user password in history when using double casting', function () {
        $passwordHash = Hash::make('password');

        $model = new TestModelWithTrait;
        $model->password = $passwordHash;
        $model->id = 123;
        $model->save();
    })->throws(PasswordAlreadyHashedException::class);

});

describe('Validation Rule', function () {
    it('should block the validation when a password is already in history', function () {
        $model = new TestModelWithTrait;
        $model->id = 123;
        $model->password = 'password';
        $model->save();

        $rule = new HasPasswordInHistory($model);

        $rule->validate('password', 'password', function ($message) {
            expect($message)->toBe('Password already in history');
        });
    });
});
