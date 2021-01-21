<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\Helper;

class Deal extends Model
{
    use Helper;
    //
    protected $fillable = [
        "id", "account_id", "bot_id", "bot_name", "account_name", "pair", "take_profit", "base_order_volume", "safety_order_volume",
        "status", "final_profit", "usd_final_profit", "final_profit_percentage", "actual_profit", "actual_usd_profit", "actual_profit_percentage",
        "safety_order_step_percentage", "martingale_coefficient", "take_profit_type", "created_at", "updated_at", "max_safety_orders", "active_safety_orders_count",
        "closed_at", "bought_volume", "bought_amount", "from_currency", "to_currency", "from_currency_id", "to_currency_id", "sold_volume", "sold_amount",
        "cancellable?", "panic_sellable?", "bought_average_price", "take_profit_price", "current_price", "finished?", "failed_message", "completed_safety_orders_count",
        "completed_safety_orders_count", "current_active_safety_orders", "reserved_base_coin", "reserved_second_coin", "deal_has_error", "type", "base_order_volume_type",
        "safety_order_volume_type", "api_key_id", "strategy_bot"
    ];

    public static function bots($api_key_id): array
    {
        $bots = [];
        $user = auth()->user();
        $api = self::api_key();
        $parent = $api['parent'];

        self::select('bot_id', 'bot_name')->where('api_key_id', '=', $api_key_id)->where(function ($query) use ($user, $parent) {
            if ($parent) {
                return $query->whereIn("account_id", $user->accounts);
            }
            return $query;
        })->get()->map(function ($item) use (&$bots) {
            $bots[$item['bot_id']] = $item['bot_name'];
        });

        $bts = [];

        foreach ($bots as $k => $v) {
            $bts[] = [
                'id' => $k,
                'name' => $v
            ];
        }

        return $bts;
    }

    public function setCreatedAtAttribute($value)
    {
        if (isset($value)) {
            $time = strtotime($value);
            $this->attributes['created_at'] = date('Y-m-d H:i:s', $time);
        }
    }

    public function setUpdatedAtAttribute($value)
    {
        if (isset($value)) {
            $time = strtotime($value);
            $this->attributes['updated_at'] = date('Y-m-d H:i:s', $time);
        }
    }

    public function setClosedAtAttribute($value)
    {
        if (isset($value)) {
            $time = strtotime($value);
            $this->attributes['closed_at'] = date('Y-m-d H:i:s', $time);
        }
    }

    public function bot()
    {
        return $this->hasOne('App\Bot');
    }
}
