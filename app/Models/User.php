<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Kutia\Larafirebase\Facades\Larafirebase;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'mobile',
        'country_code',
        'dob',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'tax_id',
        'tax_id_type',
        'funding_source',
        'employment_status',
        'employer_name',
        'occupation', //employment_position
        'public_shareholder', // is_control_person
        'is_affiliated_exchange_or_finra',
        'is_politically_exposed',
        'is_immediate_family_exposed',
        // if is_control_person or is_affiliated_exchange_or_finra
        'shareholder_company_name',
        'shareholder_company_address',
        'shareholder_company_city',
        'shareholder_company_state',
        'shareholder_company_country',
        'shareholder_company_email',
        /////////
        'account_id',
        'account_number',
        'account_type',
        'account_currency',
        'account_status',
        'watchlist_id',
        'active',
        'verified',
        'last_login',
        'profile_completion',
        'avatar',
        'ip_address',
        'bank_linked',
        'device_token',
        'doc'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token', 'password', 'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function name()
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function bank()
    {
        return $this->hasOne(Bank::class);
    }

    public function document()
    {
        return $this->hasOne(Document::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderBy('id', 'desc');
    }
    public function newNotifications()
    {
        return $this->hasMany(Notification::class)->where('is_read', 0);
    }

    public function sendNotification($title, $message)
    {
        $notification = Notification::create([
            'user_id' => $this->id,
            'title' => $title,
            'message' => $message
        ]);
        Larafirebase::withTitle($title)
                ->withBody($message)
                ->withAdditionalData(['id' => $notification->id])
                ->sendMessage($this->device_token);
    }

}
