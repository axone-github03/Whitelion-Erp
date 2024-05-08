<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('login');
        }
    }

    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if (isset($user->status) && in_array($user->status,array(1,2,3,4))) {
            $user->last_active_date_time = date('Y-m-d H:i:s');
            $user->save();
            $routeName = $request->route()->getName();

            $tokens = $user
                ->tokens()
                ->limit(PHP_INT_MAX)
                ->get();

            // echo '<pre>';
            // print_r($tokens);
            // die;

            if (isset($user->is_changed_password)) {
                if ($user->is_changed_password == 0) {
                    $passwordRouters = ['changepassword', 'do.changepassword', 'changepassword.otp', 'notification.badge'];
                    if (!in_array($routeName, $passwordRouters)) {
                        if ($user->is_changed_password == 0) {
                            return redirect()->route('changepassword');
                        }
                    }
                }
            } else {
                return redirect()->route('logout');
            }
        } else {
            return redirect()->route('logout');
        }

        return $next($request);
    }
}
