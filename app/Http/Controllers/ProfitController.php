<?php

namespace App\Http\Controllers;

use App\Deal;
use App\Bot;
use Auth;
use DB;
use App\Account;
use Illuminate\Http\Request;

class ProfitController extends Controller
{
    //
    function date()
    {
        $user = Auth::user();

        $data = array(
            'both'      => array(),
            'long'      => array(),
            'short'     => array(),
            'api_key'   => 0
        );

        if (sizeof($user->api_keys) > 0) {
            $api_key = $user->api_keys[0]->id;

            $data['api_key'] = $api_key;
            $data['both'] = DB::select($this->buildBaseQuery($api_key, "both"));
            $data['long'] = DB::select($this->buildBaseQuery($api_key, "Deal"));
            $data['short'] = DB::select($this->buildBaseQuery($api_key, "Deal::ShortDeal"));
        }

        return view('pages.profit.date', $data);
    }

    function pair()
    {
        $user = Auth::user();

        $data = array(
            'both'      => array(),
            'long'      => array(),
            'short'     => array(),
            'api_key'   => 0
        );

        if (sizeof($user->api_keys) > 0) {
            $api_key = $user->api_keys[0]->id;

            $data['api_key'] = $api_key;
            $data['both'] = DB::select($this->buildBaseQuery($api_key, "both"));
            $data['long'] = DB::select($this->buildBaseQuery($api_key, "Deal"));
            $data['short'] = DB::select($this->buildBaseQuery($api_key, "Deal::ShortDeal"));
        }

        return view('pages.profit.pair', $data);
    }

    function bot()
    {
        $user = Auth::user();

        $data = array(
            'both'      => array(),
            'long'      => array(),
            'short'     => array(),
            'api_key'   => 0
        );

        if (sizeof($user->api_keys) > 0) {
            $api_key = $user->api_keys[0]->id;

            $data['api_key'] = $api_key;
            $data['both'] = DB::select($this->buildBaseQuery($api_key, "both", "bot"));
            $data['long'] = DB::select($this->buildBaseQuery($api_key, "Deal", "bot"));
            $data['short'] = DB::select($this->buildBaseQuery($api_key, "Deal::ShortDeal", "bot"));
        }

        return view('pages.profit.bot', $data);
    }

    function getProfitByDate(Request $request)
    {
        $base = $request->input('base');
        $pair = $request->input('pair');
        $account = $request->input('account');
        $bot = $request->input('bot');
        $strategy = $request->input('strategy');
        $start = $request->input('start');
        $end = $request->input('end');
        $interval = $request->input('interval');
        $api_key = $request->input('api_key');

        if (isset($start) && isset($end))
            $range = "AND created_at BETWEEN '$start' AND '$end'\n";
        else
            $range = "";

        if ($interval == "daily")
            $interval = "DATE(created_at)";
        elseif ($interval == "weekly")
            $interval = "WEEK(created_at)";
        elseif ($interval == "monthly")
            $interval = "DATE_FORMAT(created_at, '%Y-%m')";
        elseif ($interval == "yearly")
            $interval = "YEAR(created_at)";

        if ($strategy != "%")
            $where = " AND type LIKE '{$strategy}'";
        else
            $where = "";

        if (!isset($pair) && !isset($account) && !isset($bot)) {
            $sql = "SELECT
                   'All Pairs' pair, $interval intval, SUM(final_profit) total_profit,
                   SUM(CASE WHEN deals.status in ('completed', 'panic_sold')
                   THEN 1
                   ELSE 0
                   END
                   ) as total_deals
            FROM deals
            WHERE pair LIKE '{$base}_%' {$where} AND deals.api_key_id={$api_key} $range
            AND status IN ('completed', 'stop_loss_finished' 'panic_sold', 'switched')
            AND `finished?` = 1
            GROUP BY $interval
            ORDER BY $interval ASC;";
        }elseif(isset($pair)){
            $p = isset($pair) ? "pair IN ('" . implode("','", $pair) . "')" : "";
            $a = isset($account) ? "account_id IN ('" . implode("','", $account) . "')" : "";
            $b = isset($bot) ? "bot_id IN ('" . implode("','", $bot) . "')" : "";
            $and = (isset($pair) && isset($account)) ? "AND" : "";
            $and1 = (isset($pair) && isset($bot)) || (isset($bot) && isset($account)) ? "AND" : "";

            $sql = "SELECT
                   pair, $interval intval, SUM(final_profit) total_profit,
                   SUM(CASE WHEN deals.status in ('completed', 'panic_sold')
                   THEN 1
                   ELSE 0
                   END
                   ) as total_deals
            FROM deals
            WHERE $p $and $a $and1 $b {$where} AND deals.api_key_id={$api_key} $range
            AND status IN ('completed', 'stop_loss_finished' 'panic_sold', 'switched')
            AND `finished?` = 1
            GROUP BY pair, $interval
            ORDER BY $interval ASC;";
        }else {
            $a = isset($account) ? "account_id IN ('" . implode("','", $account) . "')" : "";
            $and = (isset($bot) && isset($account)) ? "AND" : "";
            $b = isset($bot) ? "bot_id IN ('" . implode("','", $bot) . "')" : "";

            $sql = "SELECT
                   'All Pairs' pair, $interval intval, SUM(final_profit) total_profit,
                   SUM(CASE WHEN deals.status in ('completed', 'panic_sold')
                   THEN 1
                   ELSE 0
                   END
                   ) as total_deals
            FROM deals
            WHERE pair LIKE '{$base}_%' AND $a $and $b {$where} AND deals.api_key_id={$api_key} $range
            AND status IN ('completed', 'stop_loss_finished' 'panic_sold', 'switched')
            AND `finished?` = 1
            GROUP BY $interval
            ORDER BY $interval ASC;";
        }

        $report = DB::select($sql);

        $result = array();
        foreach ($report as $item) {
            if (!isset($result[$item->intval]))
                $result[$item->intval] = array();
            array_push($result[$item->intval], $item);
        }

        return response()->json($report);
    }

    public function generateReportWhere($start, $end, $interval, $strategy){
        if (isset($start) && isset($end))
            $range = "AND created_at BETWEEN '$start' AND '$end'\n";
        else
            $range = "";

        if ($interval == "daily")
            $interval = "DATE(created_at)";
        elseif ($interval == "weekly")
            $interval = "WEEK(created_at)";
        elseif ($interval == "monthly")
            $interval = "DATE_FORMAT(created_at, '%Y-%m')";
        elseif ($interval == "yearly")
            $interval = "YEAR(created_at)";

        if ($strategy != "%")
            $where = " AND type LIKE '{$strategy}'";
        else
            $where = "";

        return ['where' => $where, 'range' => $range];
    }

    function getPairByBase(Request $request)
    {
        $base = $request->input('base');
        $account = $request->input('account');
        $strategy = $request->input('strategy');
        $start = $request->input('start');
        $end = $request->input('end');
        $interval = $request->input('interval');
        $api_key = $request->input('api_key');

        $wr = $this->generateReportWhere($start, $end, $interval, $strategy);
        $where = $wr['where'];
        $range = $wr['range'];
        $whereAcc = isset($account) ? "AND account_id IN ('" . implode("','", $account) . "')" : "";

        $sql = "SELECT
            pair, SUM(final_profit) total_profit,
            SUM(CASE WHEN status in ('completed', 'panic_sold')
                THEN 1
                ELSE 0
                END
                ) as total_deals
            FROM deals
            WHERE pair LIKE '{$base}_%' {$whereAcc} {$where} AND api_key_id={$api_key} $range
            AND status IN ('completed', 'stop_loss_finished' 'panic_sold', 'switched')
            AND `finished?` = 1
            GROUP BY pair
            ORDER BY total_profit DESC;";

        $profit = DB::select($sql);

        return response()->json($profit);
    }

    function getBotByBase(Request $request)
    {
        $type = $request->input('type');
        $base = $request->input('base');
        $account = $request->input('account');
        $strategy = $request->input('strategy');
        $start = $request->input('start');
        $end = $request->input('end');
        $interval = $request->input('interval');
        $api_key = $request->input('api_key');
        if (isset($start) && isset($end))
            $range = "AND deals.created_at BETWEEN '$start' AND '$end'\n";
        else
        $range = "";
        $whereAcc = isset($account) ? "AND deals.account_id IN ('" . implode("','", $account) . "')" : "";

        $sql = "SELECT
                 deals.bot_id,
                      CASE WHEN deals.type = 'Deal' THEN 'long'
                        WHEN deals.type = 'Deal::ShortDeal' THEN 'short'
                      END as strategy,
                      COALESCE(bots.name, concat('Deleted Bot ID: ', deals.bot_id)) As name, SUM(deals.final_profit) total_profit,
                      SUM(CASE WHEN deals.status in ('completed', 'panic_sold')
                      THEN 1
                      ELSE 0
                      END
                      ) as total_deals
                FROM deals
                LEFT OUTER JOIN bots on deals.bot_id = bots.id
                WHERE deals.pair LIKE '{$base}_%' AND deals.type LIKE '{$strategy}' {$whereAcc} AND deals.api_key_id={$api_key} {$range}
                AND deals.status IN ('completed', 'stop_loss_finished', 'panic_sold', 'switched')
                AND deals.`finished?` = 1
                GROUP BY deals.bot_id, deals.type
                ORDER BY total_profit DESC;";

        $profit = DB::select($sql);

        if($type == "ACTIVE"){
            $profit = collect($profit)->filter(function($item){
                return strpos($item->name,"Deleted Bot ID:") === false;
            })->values()->all();
        }

        return response()->json($profit);
    }

    function getBasePair(Request $request)
    {
        $api_key = $request->input('api_key');
        $strategy = $request->input('strategy');
        $base = $request->input('base');

        if ($strategy == "%") {
            $sql = "SELECT
                      pair
                      FROM deals
                      WHERE deals.api_key_id=$api_key AND deals.pair IS NOT NULL AND deals.pair LIKE '{$base}_%'
                      GROUP BY pair";
        } else {
            $sql = "SELECT
                      pair
                      FROM deals
                      WHERE deals.type LIKE '$strategy' AND deals.api_key_id=$api_key AND deals.pair IS NOT NULL AND deals.pair LIKE '{$base}_%'
                      GROUP BY pair";
        }

        $pairs = DB::select($sql);

        return response()->json($pairs);
    }

    function getAccounts(Request $request)
    {
        $base = $request->input('base');
        $strategy = $request->input('strategy');
        $api_key = $request->input('api_key');
        return response()->json(Account::select("*")->where('api_key_id', '=', $api_key)->orderBy('id', 'DESC')->get());
    }

    function getBots(Request $request)
    {
        $base = $request->input('base');
        $strategy = $request->input('strategy');
        $api_key = $request->input('api_key');

        return response()->json(Deal::bots($api_key));
    }

    function buildBaseQuery($api_key, $strategy, $type = "pair")
    {
        if ($type == "pair") {
            if ($strategy == "both") {
                $sql = "SELECT
                      SUBSTRING_INDEX(pair, '_', 1) AS base
                      FROM deals
                      WHERE deals.api_key_id=$api_key AND deals.pair IS NOT NULL
                      GROUP BY base";
            } else {
                $sql = "SELECT
                      SUBSTRING_INDEX(pair, '_', 1) AS base
                      FROM deals
                      WHERE deals.type LIKE '$strategy' AND deals.api_key_id=$api_key AND deals.pair IS NOT NULL
                      GROUP BY base";
            }
        } else {
            if ($strategy == "both") {
                $sql = "SELECT
                      SUBSTRING_INDEX(pair, '_', 1) AS base
                      FROM deals
                      WHERE deals.api_key_id=$api_key AND deals.pair IS NOT NULL
                      GROUP BY base";
            } else {
                $sql = "SELECT
                      SUBSTRING_INDEX(pair, '_', 1) AS base
                      FROM deals
                      WHERE deals.type LIKE '$strategy' AND deals.api_key_id=$api_key AND deals.pair IS NOT NULL
                      GROUP BY base";
            }
        }

        return $sql;
    }
}
