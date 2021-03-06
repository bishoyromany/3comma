<?php

namespace App\Http\Controllers;

use App\Bot;
use App\Deal;
use App\Account;
use App\ThreeCommas\ThreeCommas;
use Config;
use Auth;
use Log;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Dyaa\Pushover\Facades\Pushover;
use Illuminate\Http\Request;
use App\User;
use App\PairsBlackList;
use App\Strategy;

class ThreeCommasController extends Controller
{
    private $DEBUG_MODE = false;

    //
    use ThreeCommas;

    public function __construct()
    {
        $this->loadThreeCommas();
        Log::useDailyFiles(storage_path() . '/logs/ThreeCommasController.log');
    }

    public function loadAllDeals(Request $request)
    {
        ini_set('max_execution_time', 600);
        $offset = 1000;
        $x = $request->start;
        for (; $x <= $request->end; $x++) {
            $request->merge(['offset' => $offset * $x]);
            echo $this->loadDealFrom3Commas($request);
        }

        return "done";
    }

    public function loadAllBots(Request $request)
    {
        ini_set('max_execution_time', 600);
        $offset = 100;
        $x = $request->start;
        for (; $x <= $request->end; $x++) {
            $request->merge(['offset' => $offset * $x]);
            echo $this->loadBotsFrom3Commas($request);
        }

        return "done";
    }

    public function loadDealFrom3Commas(Request $request)
    {
        $users = User::all();
        $allData = [];
        foreach ($users as $user) {
            if (sizeof($user->api_keys) > 0) {
                $limit = $request->limit ?? 10000;
                $offset = $request->offset ?? 0;
                do {
                    $response = $this->user_deals($user->api_keys[0], $limit, $offset);
                    if ($response['status'] != 200 && !env("APP_DEBUG") && $this->DEBUG_MODE) {
                        Log::critical(['user_id' => $user->id, 'username' => $user->name, 'loadDealFrom3CommasResponse' => $response['status'], 'message' => $response['response']]);
                        Pushover::push('loadDealFrom3CommasResponse', $response['response']);
                        Pushover::send();
                        break;
                    } else {
                        if ($response['status'] != 200 && env("APP_DEBUG") && $this->DEBUG_MODE) {
                            $data = json_decode(file_get_contents(public_path() . '/../tmp/deals.json'));
                        } else {
                            $data = $response['response'];
                        }
                        $allData[] = ['data' => $data, 'user' => $user, 'api' => $user->api_keys[0]];
                        foreach ($data as $json) {
                            try {
                                $new = false;
                                try {
                                    $deal = Deal::findOrFail($json->id);
                                } catch (ModelNotFoundException $e) {
                                    $new = true;
                                    $deal = new Deal();
                                }

                                $bot = Bot::find($json->bot_id);
                                if ($bot) {
                                    $json->strategy_bot = json_encode($bot->strategy_list);
                                } else {
                                    $json->strategy_bot = '[{"strategy":"NO_STRATEGY"}]';
                                }

                                $deal->fill((array)$json);
                                $deal->api_key_id = $user->api_keys[0]['id'];
                                $deal->save();
                            } catch (QueryException $exception) {
                            } catch (\Exception $e) {
                                // dd($e, $data, $json);
                                continue;
                            }
                        }
                        try {
                            $loaded = count($data);
                            $offset += count($data);
                        } catch (\Exception $e) {
                            break;
                        }
                    }
                } while ($loaded == $limit);
            }
        }

        echo "succeed \n";
    }

    public function loadBotsFrom3Commas(Request $request)
    {
        $users = User::all();
        $limit = $request->limit ?? 100;
        $offset = $request->offset ?? 100;
        foreach ($users as $user) {
            if (sizeof($user->api_keys) > 0) {
                $response = $this->user_bots($user->api_keys[0], $limit, $offset);
                if ($response['status'] == 200) {
                    $data = $response['response'];
                    foreach ($data as $json) {
                        try {
                            try {
                                $bot = Bot::findOrFail($json->id);
                            } catch (ModelNotFoundException $e) {
                                $bot = new Bot();
                            } catch (\Exception $e) {
                                dd($e, $data, $json);
                            }
                            $bot->fill((array)$json);
                            $bot->api_key_id = $user->api_keys[0]['id'];
                            $bot->save();
                        } catch (QueryException $exception) {
                        }
                    }
                } else {
                    Log::critical(['user_id' => $user->id, 'username' => $user->name, 'loadBotsFrom3CommasResponse' => $response['status'], 'message' => $response['response']]);
                    Pushover::push('loadBotsFrom3CommasResponse', $response['response']);
                    Pushover::send();
                }
            }
        }
        echo 'succeed';
    }

    public function loadAccountsFrom3Commas()
    {
        $users = User::all();
        foreach ($users as $user) {
            if (sizeof($user->api_keys) > 0) {
                $response = $this->all_accounts($user->api_keys[0]);
                if ($response['status'] == 200) {
                    $data = $response['response'];
                    foreach ($data as $json) {
                        try {
                            try {
                                $account = Account::findOrFail($json->id);
                            } catch (ModelNotFoundException $e) {
                                $account = new Account();
                            } catch (\Exception $e) {
                                dd($e, $data, $json);
                            }
                            $account->fill(
                                [
                                    'account' => (array)$json,
                                    'user_id' => $user->id,
                                    'name'    => $json->name,
                                    'id'      => $json->id
                                ]
                            );
                            $account->api_key_id = $user->api_keys[0]['id'];
                            $account->save();
                        } catch (QueryException $exception) {
                        }
                    }
                } else {
                    Log::critical(['user_id' => $user->id, 'username' => $user->name, 'loadAccountsFrom3CommasResponse' => $response['status'], 'message' => $response['response']]);
                    Pushover::push('loadAccountsFrom3CommasResponse', $response['response']);
                    Pushover::send();
                }
            }
        }

        echo 'succeed';
    }

    public function panicSellDeal($deal_id)
    {
        $user = Auth::user();
        if (sizeof($user->api_keys) > 0) {
            $response = $this->deal_panic_sell($user->api_keys[0], $deal_id);
            if ($response['status'] == 200) {
                $data = $response['response'];
                $this->loadDealFrom3Commas(new Request);
                return response()->json($data);
            } else {
                Log::critical(['user_id' => $user->id, 'username' => $user->name, 'panicSellDealResponse' => $response['status'], 'message' => $response['response']]);
                Pushover::push('panicSellDealResponse', $response['response']);
                Pushover::send();
            }
        }

        echo 'succeed';
        return back();
    }

    public function cancelDeal($deal_id)
    {
        $user = Auth::user();

        if (sizeof($user->api_keys) > 0) {
            $response = $this->deal_cancel($user->api_keys[0], $deal_id);
            if ($response['status'] == 200) {
                $data = $response['response'];
                $this->loadDealFrom3Commas(new Request);
                return response()->json($data);
            } else {
                Log::critical(['user_id' => $user->id, 'username' => $user->name, 'cancelDealResponse' => $response['status'], 'message' => $response['response']]);
                Pushover::push('cancelDealResponse', $response['response']);
                Pushover::send();
            }
        }

        echo 'succeed';
        return back();
    }

    public function disableBot($bot_id)
    {
        $user = Auth::user();

        if (sizeof($user->api_keys) > 0) {
            $response = $this->disable_bot($user->api_keys[0], $bot_id);
            if ($response['status'] == 200) {
                $data = $response['response'];
                return response()->json($data);
            } else {
                Log::critical(['user_id' => $user->id, 'username' => $user->name, 'disableBotResponse' => $response['status'], 'message' => $response['response']]);
                Pushover::push('disableBotResponse', $response['response']);
                Pushover::send();
            }
        }

        echo 'succeed';
    }

    public function enableBot($bot_id)
    {
        $user = Auth::user();

        if (sizeof($user->api_keys) > 0) {
            $response = $this->enable_bot($user->api_keys[0], $bot_id);
            if ($response['status'] == 200) {
                $data = $response['response'];
                return response()->json($data);
            } else {
                Log::critical(['user_id' => $user->id, 'username' => $user->name, 'enableBotResponse' => $response['status'], 'message' => $response['response']]);
                Pushover::push('enableBotResponse', $response['response']);
                Pushover::send();
            }
        }

        echo 'succeed';
    }

    public function startNewDeal($bot_id)
    {
        $user = Auth::user();

        if (sizeof($user->api_keys) > 0) {
            $response = $this->start_new_deal($user->api_keys[0], $bot_id);
            if ($response['status'] == 200) {
                $data = $response['response'];
                return response()->json($data);
            } else {
                Log::critical(['user_id' => $user->id, 'username' => $user->name, 'startNewDealResponse' => $response['status'], 'message' => $response['response']]);
                Pushover::push('startNewDealResponse', $response['response']);
                Pushover::send();
            }
        }

        echo 'succeed';
    }

    public function parisBlackList($array = false)
    {
        $user = Auth::user();

        if (sizeof($user->api_keys) > 0) {
            $response = $this->pairs_black_list($user->api_keys[0]);
            if ($response['status'] == 200) {
                $data = $response['response'];

                $pairs = PairsBlackList::where('api_key', '=', $user->api_keys[0]->id)->get()[0] ?? false;
                $pairsStore = [
                    'user_id' => $user->id,
                    'api_key' => $user->api_keys[0]->id,
                    'pairs'   => $data->pairs
                ];

                if ($pairs) {
                    $pairs->update($pairsStore);
                } else {
                    $pairs = PairsBlackList::create($pairsStore);
                }

                if (!$array) {
                    return response()->json($data);
                } else {
                    return $data;
                }
            } else {
                Log::critical(['user_id' => $user->id, 'username' => $user->name, 'parisBlackList' => $response['status'], 'message' => $response['response']]);
                Pushover::push('parisBlackList', $response['response']);
                Pushover::send();
            }
        }

        echo 'succeed';
    }


    public function updateParisBlackList(Request $request)
    {
        if ($request->pairs) {
            $request->merge(['pairs' => json_decode($request->pairs)]);
        }
        $this->parisBlackList();
        $user = Auth::user();
        $pairs = $request->pairs;
        if (sizeof($user->api_keys) > 0 && $pairs && is_array($pairs)) {
            $response = $this->update_pairs_black_list($user->api_keys[0], $pairs);
            $this->parisBlackList();
            if ($response['status'] == 200) {
                $data = $response['response'];
                return response()->json($data);
            } else {
                Log::critical(['user_id' => $user->id, 'username' => $user->name, 'updateParisBlackList' => $response['status'], 'message' => $response['response']]);
                Pushover::push('updateParisBlackList', $response['response']);
                Pushover::send();
            }
        }

        echo 'succeed';

        return back();
    }


    public function strategyList(Request $request, $array = false)
    {
        $user = Auth::user();

        if (sizeof($user->api_keys) > 0) {
            $response = $this->strategy_list($user->api_keys[0], $request->account_id ?? false);
            if ($response['status'] == 200) {
                $data = $response['response'];
                foreach ($data as $key => $value) {
                    $strategies = Strategy::where('api_key', '=', $user->api_keys[0]->id)->where('key', '=', $key)->get()[0] ?? false;
                    $strategyStore = [
                        'user_id'  => $user->id,
                        'api_key'  => $user->api_keys[0]->id,
                        'key'      => $key,
                        'name'     => $value->name ?? $key,
                        'strategy' => $value
                    ];

                    if ($strategies) {
                        $strategies->update($strategyStore);
                    } else {
                        $strategies = Strategy::create($strategyStore);
                    }
                }


                if (!$array) {
                    return response()->json($data);
                } else {
                    return $data;
                }
            } else {
                Log::critical(['user_id' => $user->id, 'username' => $user->name, 'parisBlackList' => $response['status'], 'message' => $response['response']]);
                // Pushover::push('parisBlackList', $response['response']);
                // Pushover::send();
            }
        }

        echo 'succeed';
    }
}
