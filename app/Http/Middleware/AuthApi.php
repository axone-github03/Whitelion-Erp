<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthApi {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
	 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
	 */
	// public function handle(Request $request, Closure $next)
	// {
	//     return $next($request);
	// }

	protected function redirectTo($request) {
		if (!$request->expectsJson()) {

			$response = errorRes("Invalid request", 500);
			return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
		}
	}

	public function handle(Request $request, Closure $next) {
		// $user = Auth::guard('api')->user();
		// auth()->setDefaultDriver('api');

		if (auth()->getDefaultDriver() == 'web') {

			auth()->setDefaultDriver('api');
		}

		$user = Auth::user();
		if (isset($user)) {

			if (isset($user->status) && in_array($user->status,array(1,2,3,4))) {
				$user->last_active_date_time = date('Y-m-d H:i:s');
				$user->save();
				$routeName = $request->route()->getName();

				if (isset($user->is_changed_password)) {

					if ($user->is_changed_password == 0) {

						$passwordRouters = array('api.do.changepassword');
						if (!in_array($routeName, $passwordRouters)) {

							// if ($user->is_changed_password == 0) {
							// 	$response = errorRes("Change password", 501);
							// 	return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
							// }

						}

					}

				} else {
					$response = errorRes("Invalid request", 500);
					return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');

				}

			} else {

				$response = errorRes("Invalid request", 500);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');

			}
		} else {

			$response = errorRes("Invalid request", 500);
			return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');

		}

		return $next($request);

	}
}
