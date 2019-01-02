<?php

namespace ReactPHPLaravel\Utils;

use Illuminate\Http\Request as IlluminateRequest;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class IllumitateRequestBuilder
{
    public static function make(ServerRequestInterface $request)
    {
        IlluminateRequest::enableHttpMethodParameterOverride();

        $method = $request->getMethod();
        $headers = $request->getHeaders();
        $query = $request->getQueryParams();
        $content = $request->getBody();
        $post = array();
        $server = $request->getServerParams();
        $cookie = $request->getCookieParams();

        /*
        |--------------------------------------------------------------------------
        | Copy from \Symfony\Component\HttpFoundation\Request::createFromGlobals().
        |--------------------------------------------------------------------------
        |
        | With the php's bug #66606, the php's built-in web server
        | stores the Content-Type and Content-Length header values in
        | HTTP_CONTENT_TYPE and HTTP_CONTENT_LENGTH fields.
        |
        */
        if ('cli-server' === PHP_SAPI) {
            if (array_key_exists('HTTP_CONTENT_LENGTH', $server)) {
                $server['CONTENT_LENGTH'] = $server['HTTP_CONTENT_LENGTH'];
            }
            if (array_key_exists('HTTP_CONTENT_TYPE', $server)) {
                $server['CONTENT_TYPE'] = $server['HTTP_CONTENT_TYPE'];
            }
        }

        if (in_array(strtoupper($method), array('POST', 'PUT', 'DELETE', 'PATCH')) &&
            isset($headers['Content-Type']) && (0 === strpos($headers['Content-Type'], 'application/x-www-form-urlencoded'))
        ) {
            parse_str($content, $post);
        }
        $symfRequest = new SymfonyRequest(
            $query,
            $post,
            array(),
            $cookie, // To get the cookies, we'll need to parse the headers
            $request->getUploadedFiles(),
            array(), // Server is partially filled a few lines below
            $content
        );

        $symfRequest->setMethod($method);
        $symfRequest->headers->replace($headers);
        $symfRequest->server->set('REQUEST_URI', $request->getUri()->getPath());
        if (isset($headers['Host'])) {
            $symfRequest->server->set('SERVER_NAME', explode(':', $headers['Host'][0])[0]);
        }

        return IlluminateRequest::createFromBase($symfRequest);

    }
}