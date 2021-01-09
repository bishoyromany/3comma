<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lunaweb\EmailVerification\Traits\CanVerifyEmail;
use Lunaweb\EmailVerification\Contracts\CanVerifyEmail as CanVerifyEmailContract;
use App\Account;

class User extends Authenticatable implements CanVerifyEmailContract

{
    use Notifiable;
    use SoftDeletes;
    use CanVerifyEmail;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', "parentID", "accounts"
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        "accounts" => "array"
    ];

    public function api_keys()
    {
        return $this->hasMany('App\ApiKey');
    }

    public function exchange_keys()
    {
        return $this->hasMany('App\ExchangeKey');
    }

    public function parentUser()
    {
        return $this->hasOne("App\User", "id", "parentID");
    }

    public function getAccounts()
    {
        $accounts = [];
        if ($this->accounts && is_array($this->accounts)) {
            foreach ($this->accounts as $account) {
                $account = Account::find($account);
                if ($account) {
                    $accounts[] = $account;
                }
            }
        }

        return $accounts;
    }

    public function getApiKey()
    {
        return $this->api_keys[0]->id ?? 0;
    }
}
