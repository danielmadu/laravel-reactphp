<?php
namespace ReactPHPLaravel\Http;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class LaravelManager
{
    // @var Illuminate\Foundation\Application $app
    protected $app;

    /**
     * LaravelManager constructor.
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle(Request $request)
    {
        $response = $this->getKernel()->handle($request);
        $this->getKernel()->terminate($request, $response);
        return $response;
    }

    protected function getKernel()
    {
        return $this->app->make(Kernel::class);
    }
}