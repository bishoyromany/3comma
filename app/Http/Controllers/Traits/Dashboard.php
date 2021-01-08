<?php

/**
 * Created by PhpStorm.
 * User: mac
 * Date: 09.10.18
 * Time: 04:34
 */

namespace App\Http\Controllers\Traits;

use Auth;
use DB;
use App\Http\Controllers\Traits\Helper;
use App\Bot;

trait Dashboard
{
    use Helper;

    public function dashboardData($request)
    {
        $user = Auth::user();

        $api = $this->api_key();
        $api_key = $api['key'];
        $parent = $api['parent'];

        //TODO: create table to store these values updated by a cron rather than query the db on each dashboard view. This data might only be used during testing to verify data.
        if ($api_key) {
            $data['api_key_id'] = $api_key;
            $data['completed_deals'] = DB::table('deals')
                ->where('api_key_id', $api_key)
                ->where('finished?', 1)
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })
                ->count();

            $data['active_deals'] = DB::table('deals')
                ->where('api_key_id', $api_key)
                ->where('finished?', 0)
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })
                ->count();

            $data['active_deals_list'] = DB::table('deals')
                ->where('api_key_id', $api_key)
                ->where('finished?', 0)
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })
                ->get();

            $data['recent_completed_deals'] = DB::table('deals')
                ->where('api_key_id', $api_key)
                ->where('finished?', 1)
                ->orderBy('id', 'desc')
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })
                ->limit(10)
                ->get();

            // $data['so_sum'] = $this->getCompletedDealsSoSum($request);

            $bases = DB::table('deals')
                ->select(DB::raw('SUBSTRING_INDEX(pair, "_", 1) base'))
                ->where('api_key_id', $api_key)
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })
                ->whereNotNull('pair')
                ->groupBy('base')
                ->get();

            $data['bot_count'] = DB::table('bots')
                ->where('api_key_id', $api_key)
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })
                ->count();

            $data['active_bots'] = DB::table('bots')
                ->where('api_key_id', $api_key)
                ->where('is_enabled', true)
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })
                ->count();

            $data['active_bots_list'] = DB::table('bots')
                ->where('api_key_id', $api_key)
                ->where('is_enabled', true)
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })
                ->get();

            $data['base_profit'] = [];
            $total_completed  = 0;
            $total_panic = 0;
            $total_stop = 0;
            $total_switched = 0;
            $total_failed = 0;
            $total_cancelled = 0;
            $total_actual = 0;

            foreach ($bases as $base) {
                $base_pair = $base->base . "_%";
                $base_profit = DB::table('deals')
                    ->where('api_key_id', $api_key)
                    ->where('finished?', 1)
                    ->where('pair', 'LIKE', $base_pair)
                    ->whereIn('status', ['completed', 'panic_sold', 'stop_loss_finished'])
                    ->where(function ($query) use ($user, $parent) {
                        if ($parent) {
                            return $query->whereIn("account_id", $user->accounts);
                        }
                        return $query;
                    })
                    ->sum('final_profit');

                $base_deals = DB::table('deals')
                    ->where('api_key_id', $api_key)
                    ->where('finished?', 1)
                    ->where('pair', 'LIKE', $base_pair)
                    ->whereIn('status', ['completed', 'panic_sold', 'stop_loss_finished'])
                    ->where(function ($query) use ($user, $parent) {
                        if ($parent) {
                            return $query->whereIn("account_id", $user->accounts);
                        }
                        return $query;
                    })
                    ->count();

                $base_completed = DB::table('deals')
                    ->where('api_key_id', $api_key)
                    ->where('finished?', 1)
                    ->where('pair', 'LIKE', $base_pair)
                    ->whereIn('status', ['completed'])
                    ->where(function ($query) use ($user, $parent) {
                        if ($parent) {
                            return $query->whereIn("account_id", $user->accounts);
                        }
                        return $query;
                    })
                    ->count();

                $base_panic = DB::table('deals')
                    ->where('api_key_id', $api_key)
                    ->where('finished?', 1)
                    ->where('pair', 'LIKE', $base_pair)
                    ->whereIn('status', ['panic_sold'])
                    ->where(function ($query) use ($user, $parent) {
                        if ($parent) {
                            return $query->whereIn("account_id", $user->accounts);
                        }
                        return $query;
                    })
                    ->count();

                $base_stop = DB::table('deals')
                    ->where('api_key_id', $api_key)
                    ->where('finished?', 1)
                    ->where('pair', 'LIKE', $base_pair)
                    ->whereIn('status', ['stop_loss_finished'])
                    ->where(function ($query) use ($user, $parent) {
                        if ($parent) {
                            return $query->whereIn("account_id", $user->accounts);
                        }
                        return $query;
                    })
                    ->count();

                $base_switched = DB::table('deals')
                    ->where('api_key_id', $api_key)
                    ->where('finished?', 1)
                    ->where('pair', 'LIKE', $base_pair)
                    ->whereIn('status', ['switched'])
                    ->where(function ($query) use ($user, $parent) {
                        if ($parent) {
                            return $query->whereIn("account_id", $user->accounts);
                        }
                        return $query;
                    })
                    ->count();

                $base_failed = DB::table('deals')
                    ->where('api_key_id', $api_key)
                    ->where('finished?', 1)
                    ->where('pair', 'LIKE', $base_pair)
                    ->whereIn('status', ['failed'])
                    ->where(function ($query) use ($user, $parent) {
                        if ($parent) {
                            return $query->whereIn("account_id", $user->accounts);
                        }
                        return $query;
                    })
                    ->count();

                $base_cancelled = DB::table('deals')
                    ->where('api_key_id', $api_key)
                    ->where('finished?', 1)
                    ->where('pair', 'LIKE', $base_pair)
                    ->whereIn('status', ['cancelled'])
                    ->where(function ($query) use ($user, $parent) {
                        if ($parent) {
                            return $query->whereIn("account_id", $user->accounts);
                        }
                        return $query;
                    })
                    ->count();

                $base_actual = DB::table('deals')
                    ->where('api_key_id', $api_key)
                    ->where('finished?', 1)
                    ->where('pair', 'LIKE', $base_pair)
                    ->where(function ($query) use ($user, $parent) {
                        if ($parent) {
                            return $query->whereIn("account_id", $user->accounts);
                        }
                        return $query;
                    })
                    ->count();

                $base_unique = DB::table('deals')
                    ->where('api_key_id', $api_key)
                    ->where('finished?', 1)
                    ->where('pair', 'LIKE', $base_pair)
                    ->where(function ($query) use ($user, $parent) {
                        if ($parent) {
                            return $query->whereIn("account_id", $user->accounts);
                        }
                        return $query;
                    })
                    ->distinct()->get(['pair']);

                $base_unique = count($base_unique);

                $pair_profit = (object) [
                    'base'      => $base->base,
                    'profit'    => '+' . $base_profit,
                    'unique'    => $base_unique,
                    'completed' => $base_completed,
                    'panic'     => $base_panic,
                    'stop'      => $base_stop,
                    'switched'  => $base_switched,
                    'failed'    => $base_failed,
                    'cancelled' => $base_cancelled,
                    'actual'    => $base_actual
                ];

                array_push($data['base_profit'], $pair_profit);
                $total_completed = $total_completed + $base_completed;
                $total_panic = $total_panic + $base_panic;
                $total_stop = $total_stop + $base_stop;
                $total_switched = $total_switched + $base_switched;
                $total_failed = $total_failed + $base_failed;
                $total_cancelled = $total_cancelled + $base_cancelled;
                $total_actual = $total_actual + $base_actual;
            }

            $total_profit = (object) [
                'base'      => '',
                'profit'    => '',
                'unique'    => '',
                'completed' => $total_completed,
                'panic'     => $total_panic,
                'stop'      => $total_stop,
                'switched'  => $total_switched,
                'failed'    => $total_failed,
                'cancelled' => $total_cancelled,
                'actual'    => $total_actual
            ];

            array_push($data['base_profit'], $total_profit);
        } else {
            $data['completed_deals'] = 0;
            $data['active_deals'] = 0;
            $data['bot_count'] = 0;
            $data['active_bots'] = 0;
            $data['active_deals_list'] = [];
            $data['active_bots_list'] = [];
            $data['recent_completed_deals'] = [];
            $data['api_key_id'] = 0;
        }

        return $data;
    }

    public function getCompletedDealsSoSum($request)
    {
        $user = auth()->user();
        $api = $this->api_key();
        $api_key = $api['key'];
        $parent = $api['parent'];

        $deals = 0;
        $x = 0;
        $sos = [];
        $sum = [
            'max_safety_orders' => 0,
            'completed_safety_orders_count' => 0,
            'so' => 0,
            'deals' => 0,
        ];

        $account = $request->account;

        $dates = [$request->start, $request->end];

        DB::table('deals')
            ->where('api_key_id', $api_key)
            ->where('finished?', 1)
            ->where('deal_has_error', 0)
            ->where(function ($query) use ($account) {
                if (!empty($account)) {
                    return $query->whereIn('account_id', $account);
                }
                return $query;
            })->where(function ($query) use ($dates) {
                if ($dates[0] && $dates[1]) {
                    return $query->whereBetween("created_at", $dates);
                }

                return $query;
            })
            ->where(function ($query) use ($user, $parent) {
                if ($parent) {
                    return $query->whereIn("account_id", $user->accounts);
                }
                return $query;
            })
            ->orderBy('id', 'desc')
            ->get()->map(function ($item) use (&$deals, &$sos, &$x, &$sum) {
                $key = "$item->completed_safety_orders_count / $item->max_safety_orders";
                if (!isset($sos[$key])) {
                    $deals = 0;
                    $sos[$key] = [
                        'so' => $key,
                        'max_safety_orders' => $item->max_safety_orders,
                        'completed_safety_orders_count' => $item->completed_safety_orders_count,
                        'deals' => $deals,
                    ];
                }

                $deals += 1;
                $sos[$key]['deals'] = $deals;
                $sos[$key]['so'] = $key;

                return $item;
            });

        return [
            'so' => collect($sos)->values()->toArray(),
            'sum' => $sum,
        ];
    }

    public function getProfit($request)
    {
        $data = [];
        $user = auth()->user();
        $api = $this->api_key();
        $api_key = $api['key'];
        $parent = $api['parent'];
        $account = $request->account ?? [];
        $bots = false;
        $strategy = $request->strategy ?? "both";

        if ($strategy != "both" && !empty($strategy)) {
            $bots = Bot::all()
                ->filter(function ($item) use ($strategy) {
                    $st = $item->strategy_list;
                    $done = false;
                    foreach ($st as $sk) {
                        foreach ($sk as $key => $value) {
                            if ($key == 'strategy' && $value == $strategy) {
                                $done = true;
                            }
                        }
                    }

                    return $done;
                })->map(function ($item) {
                    return $item->id;
                })->values()->toArray();
        }

        $plan = $request->plan ?? "both";
        $dates = [$request->start, $request->end];

        $bases = DB::table('deals')
            ->select(DB::raw('SUBSTRING_INDEX(pair, "_", 1) base'))
            ->where('api_key_id', $api_key)
            ->where(function ($query) use ($user, $parent) {
                if ($parent) {
                    return $query->whereIn("account_id", $user->accounts);
                }
                return $query;
            })
            ->whereNotNull('pair')
            ->groupBy('base')
            ->get();

        $total_completed  = 0;
        $total_panic = 0;
        $total_stop = 0;
        $total_switched = 0;
        $total_failed = 0;
        $total_cancelled = 0;
        $total_actual = 0;

        foreach ($bases as $base) {
            $base_pair = $base->base . "_%";
            $base_profit = DB::table('deals')
                ->where('api_key_id', $api_key)
                ->where('finished?', 1)
                ->where('pair', 'LIKE', $base_pair)
                ->where(function ($query) use ($account) {
                    if (!empty($account)) {
                        return $query->whereIn('account_id', $account);
                    }
                    return $query;
                })->where(function ($query) use ($dates) {
                    if ($dates[0] && $dates[1]) {
                        return $query->whereBetween("created_at", $dates);
                    }

                    return $query;
                })
                ->where(function ($query) use ($plan) {
                    if (!empty($plan) && $plan != "both") {
                        return $query->where('type', '=', $plan);
                    }
                    return $query;
                })
                ->where(function ($query) use ($bots) {
                    if ($bots) {
                        return $query->whereIn('bot_id', $bots);
                    }
                    return $query;
                })
                ->whereIn('status', ['completed', 'panic_sold', 'stop_loss_finished'])
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })
                ->sum('final_profit');

            $base_deals = DB::table('deals')
                ->where('api_key_id', $api_key)
                ->where('finished?', 1)
                ->where('pair', 'LIKE', $base_pair)
                ->whereIn('status', ['completed', 'panic_sold', 'stop_loss_finished'])
                ->where(function ($query) use ($account) {
                    if (!empty($account)) {
                        return $query->whereIn('account_id', $account);
                    }
                    return $query;
                })->where(function ($query) use ($dates) {
                    if ($dates[0] && $dates[1]) {
                        return $query->whereBetween("created_at", $dates);
                    }

                    return $query;
                })
                ->where(function ($query) use ($plan) {
                    if (!empty($plan) && $plan != "both") {
                        return $query->where('type', '=', $plan);
                    }
                    return $query;
                })
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })
                ->where(function ($query) use ($bots) {
                    if ($bots) {
                        return $query->whereIn('bot_id', $bots);
                    }
                    return $query;
                })
                ->count();

            $base_completed = DB::table('deals')
                ->where('api_key_id', $api_key)
                ->where('finished?', 1)
                ->where('pair', 'LIKE', $base_pair)
                ->whereIn('status', ['completed'])
                ->where(function ($query) use ($account) {
                    if (!empty($account)) {
                        return $query->whereIn('account_id', $account);
                    }
                    return $query;
                })->where(function ($query) use ($dates) {
                    if ($dates[0] && $dates[1]) {
                        return $query->whereBetween("created_at", $dates);
                    }

                    return $query;
                })
                ->where(function ($query) use ($plan) {
                    if (!empty($plan) && $plan != "both") {
                        return $query->where('type', '=', $plan);
                    }
                    return $query;
                })
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })
                ->where(function ($query) use ($bots) {
                    if ($bots) {
                        return $query->whereIn('bot_id', $bots);
                    }
                    return $query;
                })
                ->count();

            $base_panic = DB::table('deals')
                ->where('api_key_id', $api_key)
                ->where('finished?', 1)
                ->where('pair', 'LIKE', $base_pair)
                ->whereIn('status', ['panic_sold'])
                ->where(function ($query) use ($account) {
                    if (!empty($account)) {
                        return $query->whereIn('account_id', $account);
                    }
                    return $query;
                })->where(function ($query) use ($dates) {
                    if ($dates[0] && $dates[1]) {
                        return $query->whereBetween("created_at", $dates);
                    }

                    return $query;
                })
                ->where(function ($query) use ($plan) {
                    if (!empty($plan) && $plan != "both") {
                        return $query->where('type', '=', $plan);
                    }
                    return $query;
                })
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })->where(function ($query) use ($bots) {
                    if ($bots) {
                        return $query->whereIn('bot_id', $bots);
                    }
                    return $query;
                })
                ->count();

            $base_stop = DB::table('deals')
                ->where('api_key_id', $api_key)
                ->where('finished?', 1)
                ->where('pair', 'LIKE', $base_pair)
                ->where(function ($query) use ($account) {
                    if (!empty($account)) {
                        return $query->whereIn('account_id', $account);
                    }
                    return $query;
                })->where(function ($query) use ($dates) {
                    if ($dates[0] && $dates[1]) {
                        return $query->whereBetween("created_at", $dates);
                    }

                    return $query;
                })
                ->where(function ($query) use ($plan) {
                    if (!empty($plan) && $plan != "both") {
                        return $query->where('type', '=', $plan);
                    }
                    return $query;
                })
                ->whereIn('status', ['stop_loss_finished'])
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })->where(function ($query) use ($bots) {
                    if ($bots) {
                        return $query->whereIn('bot_id', $bots);
                    }
                    return $query;
                })
                ->count();

            $base_switched = DB::table('deals')
                ->where('api_key_id', $api_key)
                ->where('finished?', 1)
                ->where('pair', 'LIKE', $base_pair)
                ->whereIn('status', ['switched'])
                ->where(function ($query) use ($account) {
                    if (!empty($account)) {
                        return $query->whereIn('account_id', $account);
                    }
                    return $query;
                })->where(function ($query) use ($dates) {
                    if ($dates[0] && $dates[1]) {
                        return $query->whereBetween("created_at", $dates);
                    }

                    return $query;
                })
                ->where(function ($query) use ($plan) {
                    if (!empty($plan) && $plan != "both") {
                        return $query->where('type', '=', $plan);
                    }
                    return $query;
                })
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })->where(function ($query) use ($bots) {
                    if ($bots) {
                        return $query->whereIn('bot_id', $bots);
                    }
                    return $query;
                })
                ->count();

            $base_failed = DB::table('deals')
                ->where('api_key_id', $api_key)
                ->where('finished?', 1)
                ->where('pair', 'LIKE', $base_pair)
                ->whereIn('status', ['failed'])
                ->where(function ($query) use ($account) {
                    if (!empty($account)) {
                        return $query->whereIn('account_id', $account);
                    }
                    return $query;
                })->where(function ($query) use ($dates) {
                    if ($dates[0] && $dates[1]) {
                        return $query->whereBetween("created_at", $dates);
                    }

                    return $query;
                })
                ->where(function ($query) use ($plan) {
                    if (!empty($plan) && $plan != "both") {
                        return $query->where('type', '=', $plan);
                    }
                    return $query;
                })
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })->where(function ($query) use ($bots) {
                    if ($bots) {
                        return $query->whereIn('bot_id', $bots);
                    }
                    return $query;
                })
                ->count();

            $base_cancelled = DB::table('deals')
                ->where('api_key_id', $api_key)
                ->where('finished?', 1)
                ->where('pair', 'LIKE', $base_pair)
                ->whereIn('status', ['cancelled'])
                ->where(function ($query) use ($account) {
                    if (!empty($account)) {
                        return $query->whereIn('account_id', $account);
                    }
                    return $query;
                })->where(function ($query) use ($dates) {
                    if ($dates[0] && $dates[1]) {
                        return $query->whereBetween("created_at", $dates);
                    }

                    return $query;
                })
                ->where(function ($query) use ($plan) {
                    if (!empty($plan) && $plan != "both") {
                        return $query->where('type', '=', $plan);
                    }
                    return $query;
                })
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })->where(function ($query) use ($bots) {
                    if ($bots) {
                        return $query->whereIn('bot_id', $bots);
                    }
                    return $query;
                })
                ->count();

            $base_actual = DB::table('deals')
                ->where('api_key_id', $api_key)
                ->where('finished?', 1)
                ->where('pair', 'LIKE', $base_pair)
                ->where(function ($query) use ($account) {
                    if (!empty($account)) {
                        return $query->whereIn('account_id', $account);
                    }
                    return $query;
                })->where(function ($query) use ($dates) {
                    if ($dates[0] && $dates[1]) {
                        return $query->whereBetween("created_at", $dates);
                    }

                    return $query;
                })
                ->where(function ($query) use ($plan) {
                    if (!empty($plan) && $plan != "both") {
                        return $query->where('type', '=', $plan);
                    }
                    return $query;
                })
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })->where(function ($query) use ($bots) {
                    if ($bots) {
                        return $query->whereIn('bot_id', $bots);
                    }
                    return $query;
                })
                ->count();

            $base_unique = DB::table('deals')
                ->where('api_key_id', $api_key)
                ->where('finished?', 1)
                ->where('pair', 'LIKE', $base_pair)
                ->where(function ($query) use ($account) {
                    if (!empty($account)) {
                        return $query->whereIn('account_id', $account);
                    }
                    return $query;
                })->where(function ($query) use ($dates) {
                    if ($dates[0] && $dates[1]) {
                        return $query->whereBetween("created_at", $dates);
                    }

                    return $query;
                })
                ->where(function ($query) use ($plan) {
                    if (!empty($plan) && $plan != "both") {
                        return $query->where('type', '=', $plan);
                    }
                    return $query;
                })
                ->where(function ($query) use ($user, $parent) {
                    if ($parent) {
                        return $query->whereIn("account_id", $user->accounts);
                    }
                    return $query;
                })->where(function ($query) use ($bots) {
                    if ($bots) {
                        return $query->whereIn('bot_id', $bots);
                    }
                    return $query;
                })
                ->distinct()->get(['pair']);

            $base_unique = count($base_unique);

            $pair_profit = (object) [
                'base'      => $base->base,
                'profit'    => '+' . $base_profit,
                'unique'    => $base_unique,
                'completed' => $base_completed,
                'panic'     => $base_panic,
                'stop'      => $base_stop,
                'switched'  => $base_switched,
                'failed'    => $base_failed,
                'cancelled' => $base_cancelled,
                'actual'    => $base_actual
            ];

            array_push($data, $pair_profit);
            $total_completed = $total_completed + $base_completed;
            $total_panic = $total_panic + $base_panic;
            $total_stop = $total_stop + $base_stop;
            $total_switched = $total_switched + $base_switched;
            $total_failed = $total_failed + $base_failed;
            $total_cancelled = $total_cancelled + $base_cancelled;
            $total_actual = $total_actual + $base_actual;
        }

        $total_profit = (object) [
            'base'      => 'Total',
            'profit'    => '',
            'unique'    => '',
            'completed' => $total_completed,
            'panic'     => $total_panic,
            'stop'      => $total_stop,
            'switched'  => $total_switched,
            'failed'    => $total_failed,
            'cancelled' => $total_cancelled,
            'actual'    => $total_actual
        ];

        array_push($data, $total_profit);

        return $data;
    }


    public function getActiveDeals($request)
    {
        $user = auth()->user();
        $api = $this->api_key();
        $api_key = $api['key'];
        $parent = $api['parent'];
        $account = $request->account;
        $dates = [$request->start, $request->end];
        $plan = $request->plan ?? "both";

        $data = DB::table('deals')
            ->where('api_key_id', $api_key)
            ->where('finished?', 0)
            ->where(function ($query) use ($account) {
                if (!empty($account)) {
                    return $query->whereIn('account_id', $account);
                }
                return $query;
            })->where(function ($query) use ($dates) {
                if ($dates[0] && $dates[1]) {
                    return $query->whereBetween("created_at", $dates);
                }

                return $query;
            })
            ->where(function ($query) use ($plan) {
                if (!empty($plan) && $plan != "both") {
                    return $query->where('type', '=', $plan);
                }
                return $query;
            })
            ->where(function ($query) use ($user, $parent) {
                if ($parent) {
                    return $query->whereIn("account_id", $user->accounts);
                }
                return $query;
            })
            ->get()->map(function ($item) {
                $token = "<input type='hidden' name='_token' value='" . csrf_token() . "' />";
                $item->id_bot = '<a href="' . route("basic.deal.show", $item->id) . '" class="label label-primary" title="Show Deal">' . $item->id . '</a><br><a href="' . route("basic.bot.show", $item->bot_id) . '" class="label label-primary" title="Show Bot">' . $item->bot_name . '</a>';
                $item->safety_trades = $item->completed_safety_orders_count . " / " . $item->active_safety_orders_count . " / " . $item->max_safety_orders;
                $item->actions = '<form action="' . route("3commas/panicSellDeal", $item->id) . '" method="POST" data-table="activeDeals" class="ajax-form">' . $token;
                $item->actions .= "<button class='btn btn-warning'>Panic Sell</button>";
                $item->actions .= "</form>";
                $item->actions .= '<form action="' . route("3commas/cancelDeal", $item->id) . '" method="POST" data-table="activeDeals" class="ajax-form">' . $token;
                $item->actions .= "<button class='btn btn-danger'>Cancel Deal</button>";
                $item->actions .= "</form>";
                return $item;
            });

        // dd($data);

        return $data;
    }
}
