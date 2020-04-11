<?php
/**
 * Created by PhpStorm.
 * User: cuongpm
 * Date: 6/22/19
 * Time: 10:49 AM
 */

namespace Cuongpm\Uploader;


abstract class UploadModel
{
    public $thumbImage;
    public $pathUpload;
    public $fileUpload;

    public $model;
    public $input;

    public function __construct($model, $input)
    {
        $this->model = $model;
        $this->input = $input;
    }

    public function setConfig()
    {
        $this->model->fileUpload = $this->fileUpload;
        $this->model->thumbImage = $this->thumbImage;
        $this->model->pathUpload = $this->pathUpload;
    }

    public function handle()
    {
        $this->setConfig();
        return $this->model->uploads($this->input);
    }
}
