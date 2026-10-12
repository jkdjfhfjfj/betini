<?php

namespace JsonStringfy\JsonStringfy\Activereq\Activeck;

use Closure;
use Facades\JsonStringfy\JsonStringfy\Activewor\{
    DH, IN, RD, BS
};

class M
{
    public function handle($request, Closure $next, $guard = null)
    {
        return $next($request);
    }
}