<?php

namespace JsonStringfy\JsonStringfy\Activereq\Activeck;

use Closure;
use Facades\JsonStringfy\JsonStringfy\Activewor\BS;

class CPd
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
