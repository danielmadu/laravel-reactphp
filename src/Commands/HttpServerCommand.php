<?php

namespace ReactPHPLaravel\Commands;

use Illuminate\Console\Command;

class HttpServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reactphp:http {action : start|stop|restart|infos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ReactPHP HTTP Server.';

    public function handle()
    {
        $this->{$this->argument('action')}();
    }

    protected function start()
    {
        $this->info('Starting a ReactPHP http server...');
        $this->laravel->make('reactphp.manager')->run();
    }
}