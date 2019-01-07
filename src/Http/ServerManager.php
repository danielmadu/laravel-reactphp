<?php

namespace ReactPHPLaravel\Http;


use Psr\Http\Message\ServerRequestInterface;
use React\Stream\WritableResourceStream;
use ReactPHPLaravel\Utils\IllumitateRequestBuilder;
use ReactPHPLaravel\Utils\ReactPHPResponseBuilder;
use Symfony\Component\Process\Process;

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

    protected function getPidFile()
    {
        return $this->app['config']->get('reactphp.server.options.pid_file');
    }

    protected function createPidFile()
    {
        file_put_contents($this->getPidFile(), getmypid());
    }

    public function removePidFile()
    {
        $pidFile = $this->getPidFile();
        if (file_exists($pidFile)) {
            unlink($pidFile);
        }
    }

    public function getPid()
    {
        $pid = null;
        $pidPath = $this->getPidFile();
        if(file_exists($pidPath)) {
            $pid = (int) file_get_contents($pidPath);

            if(!$pid) {
                $this->removePidFile();
            }
        }

        return $pid;
    }

    public function stop()
    {
        $process = new Process(['kill', 15, $this->getPid()]);
        $process->run();

        return $process->isSuccessful();
    }

    protected function isRunning()
    {
        $pid = $this->getPid();
        if(!$pid) {
            return false;
        }

        return true;
    }

    public function run()
    {
        $this->createPidFile();
        $writable = new WritableResourceStream(STDOUT, $this->app['reactphp.loop']);
        $writable->write("\nListening on {$this->app['reactphp.socket']->getAddress()}\n");
        $this->app['reactphp.server']->listen($this->app['reactphp.socket']);
        $this->app['reactphp.loop']->run();
    }
}