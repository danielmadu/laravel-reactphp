<?php

namespace ReactPHPLaravel\Http;


use Psr\Http\Message\ServerRequestInterface;
use React\Stream\WritableResourceStream;
use ReactPHPLaravel\Utils\IllumitateRequestBuilder;
use ReactPHPLaravel\Utils\ReactPHPResponseBuilder;

class ServerManager
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
        $this->initialize();
    }

    protected function initialize()
    {
        $this->bindLaravel();
        $this->bindListeners();
    }

    protected function bindListeners()
    {
        $this->app['reactphp.server']->on('error', function (\Throwable $e) {
            echo  'Error: ' . $e->getMessage() . PHP_EOL;
            echo  'File: ' . $e->getFile() . PHP_EOL;
            echo  'Line: ' . $e->getLine() . PHP_EOL;
//            echo  'Stacktrace: ' . $e->getTraceAsString() . PHP_EOL;
        });
    }

    protected function onRequest(ServerRequestInterface $request)
    {
        $request = IllumitateRequestBuilder::make($request);
        $responseLaravel = $this->app['reactphp.laravel']->handle($request);
        $response = ReactPHPResponseBuilder::make($responseLaravel);

        return $response;
    }

    protected function bindLaravel()
    {
        $this->app->singleton(LaravelManager::class, function ($app) {
            return new LaravelManager($app);
        });

        $this->app->alias(LaravelManager::class, 'reactphp.laravel');
    }

    public function run()
    {
        $writable = new WritableResourceStream(STDOUT, $this->app['reactphp.loop']);
        $writable->write("\nListening on {$this->app['reactphp.socket']->getAddress()}\n");
        $this->app['reactphp.server']->listen($this->app['reactphp.socket']);
        $this->app['reactphp.loop']->run();
    }
}