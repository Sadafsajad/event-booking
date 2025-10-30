<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    /** Mass assignable fields */
    protected $fillable = ['name', 'email', 'password'];

    /** Hidden when serialized */
    protected $hidden = ['password', 'remember_token'];

    /** Attribute casts (Laravel 11+ style) */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }
}
