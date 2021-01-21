<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Dashboard;
use Auth;
use Session;
use DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    use Dashboard;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = $this->dashboardData($request);
        $user = auth()->user();
        if ($user->parentID) {
            $user = $user->parentUser;
        }

        if (count($user->api_keys) > 0) {
            $api_key = $user->api_keys[0]->id;
        } else {
            $api_key = false;
        }

        $data['api_key'] = $api_key;
        return view('dashboard', $data);
    }

    public function data(Request $request)
    {
        $data = $this->dashboardData($request);

        return response()->json($data);
    }

    public function soSum(Request $request)
    {
        $data = $this->getCompletedDealsSoSum($request);

        return response()->json($data);
    }


    public function profit(Request $request)
    {
        $data = $this->getProfit($request);

        return response()->json($data);
    }

    public function activeDeals(Request $request)
    {
        $data = $this->getActiveDeals($request);

        return response()->json($data);
    }

    public function riskDeals(Request $request)
    {
        $data = $this->getRiskDeals($request);

        return response()->json($data);
    }
}
