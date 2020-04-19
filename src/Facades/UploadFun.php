<?php
/**
 * Created by PhpStorm.
 * User: Fight Light Diamond
 * Date: 7/25/2016
 * Time: 10:12 PM
 */

namespace Cuongpm\Uploader\Facades;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class UploadFun
{
    private $fileName = null;
    private $basePath = null;
    private $pathUploaded = null;

    public function file($input, $basePath)
    {
        $this->basePath = $basePath;
        $originalName = $input->getClientOriginalName();
        $this->fileName = $this->formatNameFile($originalName);
        $input->move($basePath, $this->fileName);
        $this->pathUploaded = $basePath . '/' . $this->fileName;
        chmod($this->pathUploaded, 0777);
        $path = str_replace(config('filesystems.disks.public.root'), "", $this->pathUploaded);

        return $path;
    }

    public function images($input, $basePath, $thumbImages)
    {
        $path = $this->file($input, $basePath);
        $this->saveThumbs($thumbImages);

        return $path;
    }

    private function saveThumbs($thumbImages)
    {
        foreach ($thumbImages as $sizes) {
            foreach ($sizes as $size) {
                $this->savingThumb($size);
            }
        }
    }

    private function savingThumb($size)
    {
        $thumbPath = $this->getThumbPath($size);

        if ($size[0] === NULL || $size[1] === NULL) {
            Image::make($this->pathUploaded)->resize($size[0], $size[1], function ($constraint) {
                $constraint->aspectRatio();
            })->save($thumbPath);
        } else {
            Image::make($this->pathUploaded)->resize($size[0], $size[1])->save($thumbPath);
        }

        chmod($thumbPath, 0777);
    }

    private function getThumbPath($size)
    {
        $sizeImage = '_' . implode('_', $size) . '.';
        $imageName = str_replace('.', $sizeImage, $this->fileName);

        return $this->basePath . '/' . $imageName;
    }

    public function formatNameFile($name)
    {
        $arrayElement = explode('.', $name);
        $tail = array_pop($arrayElement);
        return Str::random(8) . uniqid() . '.' . $tail;
    }
}
