<?php
namespace Abs\BasicPkg\API;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class AuthController extends Controller {
	public $successStatus = 200;

	public function login(Request $request) {
		$validator = Validator::make($request->all(), [
			'username' => 'required|string',
			'password' => 'required|string',
			'imei' => 'required|string',
		]);
		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'error' => 'Required parameters are missing',
				'errors' => $validator->errors(),
			], $this->successStatus);
		}

		if (!Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
			if (!Auth::attempt(['mobile_number' => $request->username, 'password' => $request->password])) {
				if (!Auth::attempt(['email' => $request->username, 'password' => $request->password])) {
					return response()->json([
						'success' => false,
						'error' => 'Invalid username / password',
					], $this->successStatus);
				}
			}
		}
		$user = Auth::user();
		$user->permissions = $user->perms();
		$user->token = $user->createToken('mobile_v2')->accessToken;
		$user->entity();
		// $user->entity;
		return response()->json([
			'success' => true,
			'user' => $user,
		], $this->successStatus);

	}
}
