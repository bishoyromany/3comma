<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/clear-cache', function () {
     $exitCode = \Artisan::call('config:clear');
     return $exitCode;
});

Route::get('/migrate', function () {
     $exitCode = \Artisan::call('migrate');
     return $exitCode;
});

Route::get('/', function () {
     return view('welcome');
});

Route::group(['middleware' => ['web', 'auth', /*'isEmailVerified'*/]], function () {
     Route::get('/dashboard', 'DashboardController@index')->name("dashboard");
     Route::get('/dashboard/data', 'DashboardController@data')->name('dashboard/data');
     Route::post('/dashboard/soSum', 'DashboardController@soSum')->name('dashboard/soSum');
     Route::post('/dashboard/profit', 'DashboardController@profit')->name('dashboard/profit');
     Route::post('/dashboard/activeDeals', 'DashboardController@activeDeals')->name('dashboard/activeDeals');

     Route::get('/apikey', 'ApiKeyController@index');
     Route::get('/apikey/create', 'ApiKeyController@create');
     Route::post('/apikey/store', 'ApiKeyController@store')->name('apikey/store');

     Route::get('/exchangekey', 'ExchangeKeyController@index');
     Route::get('/exchangekey/create', 'ExchangeKeyController@create');
     Route::post('/exchangekey/store', 'ExchangeKeyController@store')->name('exchangekey/store');

     Route::post('/3commas/panicSellDeal/{deal_id}', 'ThreeCommasController@panicSellDeal')->name('3commas/panicSellDeal');
     Route::post('/3commas/cancelDeal/{deal_id}', 'ThreeCommasController@cancelDeal')->name('3commas/cancelDeal');
     Route::post('/3commas/disableBot/{bot_id}', 'ThreeCommasController@disableBot')->name('3commas/disableBot');
     Route::post('/3commas/enableBot/{bot_id}', 'ThreeCommasController@enableBot')->name('3commas/enableBot');
     Route::post('/3commas/startNewDeal/{bot_id}', 'ThreeCommasController@startNewDeal')->name('3commas/startNewDeal');
     Route::post('/3commas/updateParisBlackList', 'ThreeCommasController@updateParisBlackList')->name('3commas/updateParisBlackList');

     Route::get('/profit/date', 'ProfitController@date');
     Route::get('/profit/pair', 'ProfitController@pair')->name("profit.pair");
     Route::get('/profit/bot', 'ProfitController@bot');
     Route::get('/profit/strategy', 'ProfitController@strategy');
     Route::post('/profit/getPairByBase', 'ProfitController@getPairByBase')->name('profit/getPairByBase');
     Route::post('/profit/strategyByBase', 'ProfitController@getStrategyByBase')->name('profit/getStrategyByBase');
     Route::post('/profit/getBotByBase', 'ProfitController@getBotByBase')->name('profit/getBotByBase');
     Route::post('/profit/getProfitByDate', 'ProfitController@getProfitByDate')->name('profit/getProfitByDate');
     Route::post('/profit/getBasePair', 'ProfitController@getBasePair')->name('profit/getBasePair');
     Route::post('/profit/getAccounts', 'ProfitController@getAccounts')->name('profit/getAccounts');
     Route::post('/profit/getBots', 'ProfitController@getBots')->name('profit/getBots');
     Route::post('/profit/getStrategies', 'ProfitController@getStrategies')->name('profit/getStrategies');
     Route::get('/calculator/longBot', 'CalculatorController@longBot');
     Route::get('/calculator/shortBot', 'CalculatorController@shortBot');

     Route::get('/pairs', 'PairsController@index')->name("pairs");
     Route::get('/active/deals', 'DealController@activeDeals')->name("active.deals");

     Route::get('/plan', 'PlanController@index');

     Route::get('/profile', 'ProfileController@index');
     Route::post('/profile', 'UserController@update_avatar');
     /**
      * accounts 
      */
     Route::get('/user/accounts/index', 'UserController@userAccountsIndex')->name("user.accounts.index");
     Route::get('/user/accounts/data', 'UserController@userAccountsData')->name("user.accounts.data");
     Route::post('/user/accounts/store', 'UserController@userAccountsStore')->name("user.accounts.store");
     Route::get('/user/accounts/delete/{id}', 'UserController@userAccountsDelete')->name("user.accounts.delete");


     Route::get('/basic/bot', 'BotController@index');
     Route::get('/basic/bot/{bot}', 'BotController@show')
          ->name('basic.bot.show')
          ->where('id', '[0-9]+');

     Route::get('/basic/deal', 'DealController@index');
     Route::get('/basic/deal/{deal}', 'DealController@show')
          ->name('basic.deal.show')
          ->where('id', '[0-9]+');

     Route::get('register/verify', 'App\Http\Controllers\Auth\RegisterController@verify')->name('verifyEmailLink');
     Route::get('register/verify/resend', 'App\Http\Controllers\Auth\RegisterController@showResendVerificationEmailForm')->name('showResendVerificationEmailForm');
     Route::post('register/verify/resend', 'App\Http\Controllers\Auth\RegisterController@resendVerificationEmail')->name('resendVerificationEmail');

     Route::get('/smartswitch/dual', 'SmartSwitchDualController@index');
     Route::post('/smartswitch/dual/store', 'SmartSwitchDualController@store')->name('smartswitch/dual/store');
     Route::get('/smartswitch/dual/create', 'SmartSwitchDualController@create')->name('smartswitch/dual/create');
     Route::get('/smartswitch/dual/{smart_switch_dual}', 'SmartSwitchDualController@show')
          ->name('smartswitch.dual.show')
          ->where('id', '[0-9]+');



     $name = 'scheduler';
     Route::get('/scheduler', "SchedulerController@index")->name("$name.index");
     Route::get("/scheduler/edit/{task}", "SchedulerController@edit")->name("$name.edit");
     Route::patch("/scheduler/update/{task}", "SchedulerController@update")->name("$name.update");
     Route::get("/scheduler/toggle/{task}", "SchedulerController@toggle")->name("$name.toggle");
     Route::get("/scheduler/run/{task}", "SchedulerController@run")->name("$name.run");
     Route::get("/scheduler/create", "SchedulerController@create")->name("$name.create");
     Route::post("/scheduler/store", "SchedulerController@store")->name("$name.store");
     Route::delete("/scheduler/delete", "SchedulerController@delete")->name("$name.delete");
});

Route::get('/run/monitor', 'Monitor@index')->name("monitor/start");
Route::get('/stop/monitor', 'Monitor@index')->name("monitor/stop");


Route::get('/3commas/strategyList', 'ThreeCommasController@strategyList')->name('3commas/strategyList');
Route::get('/3commas/loadDeal', 'ThreeCommasController@loadDealFrom3Commas')->name('3commas/loadDeal');
Route::get('/3commas/loadDeal/all', 'ThreeCommasController@loadAllDeals')->name('3commas/loadDeal/all');
Route::get('/3commas/loadBots', 'ThreeCommasController@loadBotsFrom3Commas')->name('3commas/loadBots');
Route::get('/3commas/loadBots/all', 'ThreeCommasController@loadAllBots')->name('3commas/loadBots/all');
Route::get('/3commas/loadAccounts', 'ThreeCommasController@loadAccountsFrom3Commas')->name('3commas/loadAccounts');
Route::get('/3commas/parisBlackList', 'ThreeCommasController@parisBlackList')->name('3commas/parisBlackList');
