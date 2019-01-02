<?php

namespace ReactPHPLaravel\Utils;


use Illuminate\Http\Response;
use React\Http\Response as ReactResponse;

class ReactPHPResponseBuilder
{
    public static function make(Response $response)
    {
        return new ReactResponse(
            $response->getStatusCode(),
            $response->headers->all(),
            $response->getContent()
        );
    }
}