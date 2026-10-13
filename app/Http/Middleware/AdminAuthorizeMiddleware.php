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
        $routeName = optional($request->route())->getName();
        $access = adminAccessList($user);
        $walletPermissions = [
            'admin.wallet-providers' => ['admin.wallet-providers', 'admin.payment.methods'],
            'admin.wallet-providers.save' => ['admin.wallet-providers.save', 'admin.edit.payment.methods'],
            'admin.wallet-providers.rates' => ['admin.wallet-providers.rates', 'admin.edit.payment.methods'],
            'admin.wallet-providers.register-channel' => ['admin.wallet-providers.register-channel', 'admin.deposit.manual.create'],
            'admin.wallet-manual.index' => ['admin.wallet-manual.index', 'admin.payment.pending', 'admin.payment.log', 'admin.payment.methods'],
            'admin.wallet-manual.review' => ['admin.wallet-manual.review', 'admin.payment.action', 'admin.payment.methods'],
        ];
        if (isset($walletPermissions[$routeName])) {
            if ($access->intersect($walletPermissions[$routeName])->isNotEmpty()) {
                return $next($request);
            }
            return redirect()->route('admin.403');
        }

        $list = collect(config('role'))->pluck('access')->flatten();

        if (!$list->contains($routeName) || $access->contains($routeName)) {
            return $next($request);
        }

        return  redirect()->route('admin.403');
    }
}
