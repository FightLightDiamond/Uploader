<?php

namespace Uploader\Commands;

use Illuminate\Console\Command;

class MakeUploaderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:uploader {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create A New Eloquent Model Uploader';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
    }
}
