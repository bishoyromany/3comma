<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Bot;

class Account extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = ['id', 'user_id', 'api_key_id', 'account', 'name'];

    protected $table = 'accounts';

    protected $casts = [
        'account' => 'array',
    ];

    public function bots()
    {
        return Bot::where('account_id', '=', $this->id)->get();
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
