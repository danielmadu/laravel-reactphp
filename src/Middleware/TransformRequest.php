<?php

namespace ReactPHPLaravel\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use ReactPHPLaravel\Utils\IllumitateRequestBuilder;

final class TransformRequest
{
    public function __invoke(ServerRequestInterface $request, callable $next)
    {
//        $request = IllumitateRequestBuilder::make($request);
        return $next($request);
    }
}