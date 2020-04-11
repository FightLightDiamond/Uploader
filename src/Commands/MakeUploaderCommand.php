<?php

namespace Cuongpm\Uploader\Commands;

use Illuminate\Console\Command;

class MakeUploaderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:uploader {class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create A New Eloquent Model Uploader';

    private $source;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->source = file_get_contents(__DIR__ . '/../stubs/model-uploader.stubs');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $class = $this->argument('class');
        $namespace = "App\Uploads";
        $data = compact('class', 'namespace');

        $this->building($data);
        $this->createFile($class);
    }

    private function createFile($class)
    {
        $fileForm = fopen($this->getSource($class), "w");
        fwrite($fileForm, $this->source);
    }

    private function getSource($class, $path = 'app')
    {
        if (!is_dir(base_path($path . '/Uploads'))) {
            try {
                mkdir(base_path($path . '/Uploads'));
            } catch (\Exception $exception) {
                dump($exception->getMessage());
            }
        }

        return base_path($path . '/Uploads/' . $class . '.php');
    }

    public function building($data)
    {
        $this->buildClass($data['class']);
        $this->buildNameSpace($data['namespace']);
    }

    protected function buildClass($class)
    {
        $this->working('_class_', $class);
    }

    protected function buildNameSpace($namespace)
    {
        $this->working('_namespace_', $namespace);
    }

    protected function working($changed, $material)
    {
        $this->source = str_replace($changed, $material, $this->source);
    }
}
