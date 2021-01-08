<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;

class UserController extends Controller
{
	public function update_avatar(Request $request)
	{
		$this->validate($request, [
			'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
		]);

		$user = Auth::user();
		$avatarName = $user->id . '_avatar' . time() . '.' . request()->avatar->getClientOriginalExtension();
		$request->avatar->storeAs('avatars', $avatarName);
		$user->avatar = $avatarName;
		$user->save();
		return back()
			->with('success', 'Profile image uploaded successfuly.');
	}


	public function userAccountsStore(Request $request)
	{
		$request->merge(['accounts' => $request->accounts ? json_decode($request->accounts) : ""]);
		$this->validate($request, [
			'id' => 'required',
			'accounts' => 'required|array',
		]);

		$user = User::find($request->id);

		if (!$user) {
			return back()->with("errors", ["Failed To Find The User You Selected"]);
		}

		$user->update([
			'parentID' => auth()->user()->id,
			'accounts' => $request->accounts
		]);

		return back()->with("successResponse", ["User Parent Added Successfully"]);
	}


	public function userAccountsIndex()
	{
		$api_key = auth()->user()->api_keys()->get()[0]->id ?? "";
		return view("pages.users.index")->with(['api_key' => $api_key]);
	}

	public function userAccountsData()
	{
		$id = auth()->user()->id;
		return response()->json([
			'users' => User::where('id', '!=', $id)->where(function ($query) use ($id) {
				return $query->where("parentID", '!=', $id)->orWhereNull('parentID');
			})->get(),
			'myusers' => User::where('parentID', '=', $id)->get()->map(function ($item) {
				$item->accountsData = $item->getAccounts();
				return $item;
			})
		]);
	}


	public function userAccountsDelete($id)
	{
		User::where('id', '=', $id)->where('parentID', '=', auth()->user()->id)->update(['parentID' => null, 'accounts' => null]);
		return back()->with("successResponse", ["User Parent Removed Successfully"]);
	}
}
