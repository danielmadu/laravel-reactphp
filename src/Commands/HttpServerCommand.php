<?php

namespace ReactPHPLaravel\Commands;

use Illuminate\Console\Command;
use ReactPHPLaravel\Http\ServerManager;

class HttpServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reactphp:http {action : start|stop|restart|infos}';

    /**
     * @var ServerManager
     */
    protected $manager;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ReactPHP HTTP Server.';

    protected function init()
    {
        $this->manager = $this->laravel->make('reactphp.manager');
    }

    public function handle()
    {
        $this->init();
        $this->{$this->argument('action')}();
    }


    protected function start()
    {
        $this->info('Starting the ReactPHP http server...');
        $this->manager->run();
    }

    protected function stop()
    {
        $this->info('Stopping the ReactPHP http server...');
        $isSuccessful = $this->manager->stop();

        if (!$isSuccessful) {
            $this->error('Unable to stop the ReactPHP http server process.');
            exit(1);
        }

        $this->manager->removePidFile();
    }

}