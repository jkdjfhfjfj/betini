<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;
class AdminAuthorizeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('admin')->user();
        $walletPermissions = [
            'admin.wallet-providers' => 'admin.payment.methods',
            'admin.wallet-providers.save' => 'admin.edit.payment.methods',
            'admin.wallet-providers.register-channel' => 'admin.deposit.manual.create',
        ];
        $routeName = $request->route()->getName();
        if (isset($walletPermissions[$routeName])) {
            $access = collect($user->admin_access)->flatten()->all();
            if (in_array($walletPermissions[$routeName], $access, true)) {
                return $next($request);
            }
            return redirect()->route('admin.403');
        }

        $list = collect(config('role'))->pluck(['access'])->flatten();
        $filtered = $list->intersect($user->admin_access);

        if(!in_array($routeName, $list->toArray()) ||  in_array($routeName, $filtered->toArray()) ){
            return $next($request);
        }

        return  redirect()->route('admin.403');
    }
}
