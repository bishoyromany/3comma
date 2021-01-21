<?php

namespace App\Http\Controllers;

use Auth;
use Session;
use DB;
use App\Deal;

class DealController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show All Deals.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $user = Auth::user();

        if (sizeof($user->api_keys) > 0) {

            $data['active_deals_list'] = DB::table('deals')
                ->where('api_key_id', $user->api_keys[0]->id)
                ->where('finished?', 0)
                ->get();

            $data['all_completed_deals'] = DB::table('deals')
                ->where('api_key_id', $user->api_keys[0]->id)
                ->where('finished?', 1)
                ->orderBy('id', 'desc')
                ->get();

            return view('pages.deal.list', $data);
        }
    }

    public function show($id)
    {
        $deal = Deal::findOrFail($id);

        return view('pages.deal.show', compact('deal'));
    }

    public function activeDeals()
    {
        return view('pages.deal.activeDeals', ['api_key' => auth()->user()->getApiKey()]);
    }

    public function riskDeals()
    {
        return view('pages.deal.riskDeals', ['api_key' => auth()->user()->getApiKey()]);
    }
}
