<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use App\Notifications\CustomVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'image',
        'id_document',
        'contact_number',
        'house_no',
        'street',
        'barangay_id',
        'municipality',
        'password',
        'is_active',
        'is_admin',
        'registration_status',
        'date_verified',
        'verified_by',
        'user_type',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_verified' => 'datetime',
            'password' => 'hashed',
            'registration_status' => 'boolean',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }


    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }
}
