<?php

namespace Abs\BasicPkg\Controllers\Api\Auth;

use App\Exceptions\ValidationException;
use ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Masters\Auth\User;
use Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use Validator;

class AuthenticationController extends Controller {

	public function login(Request $request) {
		$validator = Validator::make($request->all(), [
			'username' => 'required|string|max:1',
			'password' => 'required|string',
		]);

		if ($validator->fails()) {
			throw new ValidationException($validator);
		}

		$bypassAuth = $this->canBypassAuthentication();
		$response = new ApiResponse();
		if ($bypassAuth) {
			$user = User::where('username', '=', $request->username)->first();

			if ($user) {
				Auth::login($user);
			}
		}
		else if ($request->has('username') && $request->has('password')) {
			// Try to authenticate with credentials
			Auth::attempt([
				'username' => $request->username,
				'password' => $request->password,
			]);
		}

		$user = Auth::user();
		if(!$user){
			$response->setError('Invalid username or password');
			return $response->response();
		}

		// add list of permissions
		$user->addAppends([
			'permissions',
		]);
		$user->setHidden(['roles']);
		$response->setData('user', $user);

		return $response->response();
	}

	/**
	 * Logout and clear session
	 *
	 * @return JsonResponse
	 */
	public function logout() {
		if (Auth::check()) {
			Auth::user()->token()->revoke();
			Session::flush();
			return response()->json([
				'status' => true,
			], $this->successStatus);
		} else {
			return response()->json([
				'status' => false,
				'error' => 'Invalid user',
			], $this->successStatus);
		}
	}

	/**
	 * Register a login in
	 *
	 * @param User $user
	 */
	private function registerLogin(User $user) {
		$loginLog = new LoginLog();
		$loginLog->user()
			->associate($user)
		;
		$loginLog->ip = Request::getClientIp();
		$loginLog->save();
	}

	/**
	 * Check if user can use credentials to by pass authentication
	 *
	 * @return bool
	 */
	private function canBypassAuthentication() {
		if (config('app.env') === 'local') {
			return true;
		}
		return false;
	}

	public function forgotPassword(Request $request) {

		$validator = Validator::make($request->all(), [
			'email' => [
				'required',
				'exists:users,email',
			],
		]);
		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'error' => 'Validation Error',
				'errors' => $validator->errors()->all(),
			], $this->successStatus);
		}

		$user = User::where('email', $request->email)->first();

		if ($user) {
			// $otp_no = mt_rand(100000, 999999);
			// $user->otp = $otp_no;
			// $user->save();

			// $message = str_replace('OTP_NO', $otp_no, config('custom.SMS_MESSAGES.forgot_password_otp'));
			// $message = str_replace('BPAS', 'GIGO', $message);

			// sendSMSNotification($user->contact_number, $message);

			return response()->json([
				'status' => true,
				'data' => $user,
				'message' => 'Link Sent Successfully!',
			], $this->successStatus);
		} else {
			return response()->json([
				'status' => false,
				'error' => 'Incorrect Email',
			], $this->successStatus);
		}
	}

	public function changePassword(Request $request) {

		$error_messages = [
			'new_password.same' => 'Password & Confirm Password must be same',
			'new_password.regex' => 'Password must be atleast one upper case letter and atleast one numeric value and atleast one special character',
			'new_password.min' => 'Password must be more than 6 characters long',
		];

		$validator = Validator::make($request->all(), [
			'user_id' => [
				'required',
				'integer',
				'exists:users,id',
			],
			'new_password' => [
				'required',
				'min:6',
				'regex:/[a-z]/',
				'regex:/[A-Z]/',
				'regex:/[0-9]/',
				'regex:/[@$!%*#?&]/',
				// 'confirmed',
				'same:new_password_confirmation',
			],
			'new_password_confirmation' => [
				'required',
				'min:6',
			],
		], $error_messages);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'error' => 'Validation Error',
				'errors' => $validator->errors()->all(),
			], $this->successStatus);
		}

		if ($request->user_id) {
			$user = User::find($request->user_id);

			if ($user) {
				$user->password = $request->new_password;
				$user->save();
				return response()->json([
					'status' => true,
					'data' => $user,
					'message' => 'Password Reset Successfully!',
				], $this->successStatus);
			} else {
				return response()->json([
					'status' => false,
					'error' => 'Invalid User',
				], $this->successStatus);
			}
		}

	}

}
