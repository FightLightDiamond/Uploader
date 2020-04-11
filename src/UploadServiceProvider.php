<?php

namespace Cuongpm\Uploader;

use Cuongpm\Uploader\Facades\UploadFun;
use Illuminate\Support\ServiceProvider;
use Cuongpm\Uploader\Commands\MakeUploaderCommand;

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

        $this->app->bind('UploadFa', UploadFun::class);
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
