<?php

namespace JsonStringfy\JsonStringfy\Activereq\Activeck;

use Closure;
use Facades\JsonStringfy\JsonStringfy\Activewor\IN;

class WTC
{
    public function handle($request, Closure $next)
    {
		return $next($request);
    }
}
