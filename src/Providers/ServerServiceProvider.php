<?php
namespace ReactPHPLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use React\Http\Server;
use ReactPHPLaravel\Commands\HttpServerCommand;
use ReactPHPLaravel\Http\LaravelManager;
use ReactPHPLaravel\Http\ServerManager;
use ReactPHPLaravel\Middleware\TransformRequest;
use ReactPHPLaravel\Utils\IllumitateRequestBuilder;
use ReactPHPLaravel\Utils\ReactPHPResponseBuilder;

class ServerServiceProvider extends ServiceProvider
{
    protected $defer = false;
    /**
     * @var \React\Http\Server | \React\Socket\Server
     */
    protected static $server;

    public function register()
    {
        $this->mergeConfigs();
        $this->registerLoop();
        $this->registerLaravel();
        $this->registerSocket();
        $this->registerServer();
        $this->registerManager();
        $this->registerCommands();
    }

    protected function mergeConfigs()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/reactphp_server.php', 'reactphp');
    }

    protected function registerLoop()
    {
        $this->app->singleton('React\EventLoop\LoopInterface', function ($app) {
            return \React\EventLoop\Factory::create();
        });

        $this->app->alias('React\EventLoop\LoopInterface', 'reactphp.loop');
    }

    protected function registerSocket()
    {
        $this->app->singleton('React\Socket\Server', function ($app) {
            return new \React\Socket\Server(
                "{$app['config']->get('reactphp.server.host')}:{$app['config']->get('reactphp.server.port')}",
                $app->make('reactphp.loop')
            );
        });

        $this->app->alias('React\Socket\Server', 'reactphp.socket');
    }

    protected function registerServer()
    {
        $this->app->singleton('reactphp.server', function ($app) {
            return new Server([
                function (ServerRequestInterface $request) use ($app) {
                    $request = IllumitateRequestBuilder::make($request);
                    $responseLaravel = $app['reactphp.laravel']->handle($request);
                    $response = ReactPHPResponseBuilder::make($responseLaravel);
                    return $response;
                }
            ]);
        });
    }

    protected function registerCommands()
    {
        $this->commands([
            HttpServerCommand::class
        ]);
    }

    /**
     * Register server manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton('reactphp.manager', function ($app) {
            return new ServerManager($app);
        });
    }

    protected function registerLaravel()
    {
        $this->app->singleton(LaravelManager::class, function ($app) {
            return new LaravelManager($app);
        });

        $this->app->alias(LaravelManager::class, 'reactphp.laravel');
    }
}