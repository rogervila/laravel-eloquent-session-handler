<?php

namespace Tests\EloquentSessionHandler\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    protected $guarded = [];

    public static function fake(array $fields = []): self
    {
        return self::create(array_merge([
            'name' => 'foo',
            'email' => 'foo@bar.com',
            'password' => Hash::make('secret'),
        ], $fields));
    }
}
