<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ApiLogger;
use Illuminate\Http\Request;

class Api {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
	 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
	 */
	public function handle(Request $request, Closure $next) {

		
		if (getCheckAppVersion($request->app_source, $request->app_version)) {
			$logEntry = new ApiLogger();
			$logEntry->url = $request->fullUrl();
			$logEntry->method = $request->method();
			$logEntry->body = json_encode($request->all());
			$logEntry->header = json_encode($request->header());
			$logEntry->ip = $request->ip();
			$logEntry->status_code = http_response_code();
			$logEntry->user_agent = $request->header('User-Agent');
			$logEntry->remark = 'API';
			$logEntry->save();
			return $next($request);
		} else {
			$response = quoterrorRes(2, 402, "Please Update Your App");
			return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
		}

	}
}
