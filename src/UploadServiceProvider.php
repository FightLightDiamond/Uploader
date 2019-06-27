<?php

namespace Uploader\Providers;

use Illuminate\Support\ServiceProvider;
use Uploader\Commands\MakeUploaderCommand;

class UploadServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            __DIR__.'/../config/uploader.php' => config_path('uploader.php'),
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands(MakeUploaderCommand::class);
    }
}
