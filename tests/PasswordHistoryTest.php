<?php

use Beliven\PasswordHistory\Entities\Enums\DomainErrorsEnum;
use Beliven\PasswordHistory\Exceptions\PasswordInHistoryException;
use Beliven\PasswordHistory\Facades\PasswordHistory as PasswordHistoryFacade;
use Beliven\PasswordHistory\Models\PasswordHash;
use Beliven\PasswordHistory\PasswordHistory;
use Beliven\PasswordHistory\Rules\HasPasswordInHistory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->passwordHistory = new PasswordHistory;

    DB::table('password_hashes')->truncate();
    Config::set('password-history.depth', 5);
});

describe('Password history facade', function () {
    it('should use the facade', function () {
        $model = TestModel::create();
        $result = PasswordHistoryFacade::hasPasswordInHistory($model, 'password');

        expect($result)->toBeFalse();
    });
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
    })->throws(PasswordInHistoryException::class, DomainErrorsEnum::PASSWORD_IN_HISTORY->message());

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
    })->throws(PasswordInHistoryException::class, DomainErrorsEnum::PASSWORD_IN_HISTORY->message());

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

        $this->assertDatabaseMissing('password_hashes', [
            'model_type' => get_class($model),
            'model_id'   => $model->id,
        ]);

        expect($model->password)->toBe($passwordHash);
    });

    it('should not save any history hash using null value', function () {
        $model = new TestModelWithTrait;
        $model->id = 388888;
        $model->password = null;
        $model->save();

        $this->assertDatabaseMissing('password_hashes', [
            'model_type' => get_class($model),
            'model_id'   => $model->id,
        ]);

        // In a real application, the database will throw an error because the password field is not nullable
        expect($model->password)->toBeNull();
    });

    it('should not save any history hash updating another model property', function () {
        $model = new TestModelWithTrait;
        $model->id = 388888;
        $model->name = 'test';
        $model->save();

        $model->name = 'test1';
        $model->save();

        $this->assertDatabaseMissing('password_hashes', [
            'model_type' => get_class($model),
            'model_id'   => $model->id,
        ]);
    });

    it('should not save any history hash updating to null the password property', function () {
        $model = new TestModelWithTrait;
        $model->id = 388888;
        $model->name = 'test';
        $model->save();

        $model->password = null;
        $model->save();

        $this->assertDatabaseMissing('password_hashes', [
            'model_type' => get_class($model),
            'model_id'   => $model->id,
        ]);
    });

});

describe('Validation Rule', function () {
    it('should block the validation when a password is already in history', function () {
        $model = new TestModelWithTrait;
        $model->id = 123;
        $model->password = 'password';
        $model->save();

        $rule = new HasPasswordInHistory($model);

        $rule->validate('password', 'password', function ($message) {
            expect($message)->toBe(DomainErrorsEnum::PASSWORD_IN_HISTORY->message());
        });
    });
});
