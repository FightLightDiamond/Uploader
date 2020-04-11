<?php
/**
 * Created by PhpStorm.
 * User: cuongpm
 * Date: 6/19/19
 * Time: 10:20 AM
 */

namespace Cuongpm\Uploader;


use Illuminate\Support\Facades\Log;
use Cuongpm\Uploader\Facades\UploadFa;

trait UploadAble
{
    private $input;

    private function getBasePath()
    {
        return config('filesystems.disks.public.root');
    }

    public function uploader($input)
    {
        $uploader = $this->getModelUploadClass();
        $u = new $uploader($this, $input);

        return $u->handle();
    }

    public function uploaderSave($input)
    {
        $this->uploader($input);
        $this->fill($input);
        return $this->save();
    }

    public function uploads($input)
    {
        $this->input = $input;

        if (isset($this->fileUpload)) {
            foreach ($this->pathUpload as $name => $type) {
                if (isset($input[$name]) && request()->hasFile($name)) {
                    $this->doing($name, $type);
                }
            }
        }

        return $this->input;
    }

    private function doing($name, $type)
    {
        if (is_array($this->input[$name])) {
            $this->multi($name, $type);
            return;
        }

        $this->processUploads($name, $type);
        $this->removeFileExits($name);
    }

    private function multi($name, $type)
    {
        $folder = $this->pathUpload[$name] ?? '';

        $link = $this->generatePath($folder);

        foreach ($this->input[$name] as $index => $value) {
            $item = $this->input[$name][$index];

            if ($type === 0) {
                $this->input[$name][$index] = UploadFa::file($item, $link);
                continue;
            }

            $this->input[$name][$index] = UploadFa::images($item, $link, $this->thumbImage[$name] ?? []);
            continue;
        }
    }

    private function processUploads($name, $key)
    {
        $folder = $this->pathUpload[$name] ?? '';
        $link = $this->generatePath($folder);

        if ($key === 0) {
            $this->input[$name] = UploadFa::file($this->input[$name], $link);
        } else {
            $thumbs = $this->thumbImage[$name] ?? [];
            $this->input[$name] = UploadFa::images($this->input[$name], $link, $thumbs);
        }
    }

    private function generatePath($folder)
    {
        $basePath = $this->getBasePath();

        if (!file_exists($basePath)) {
            mkdir($basePath, 0777, true);
        }
        $basePath .= $folder;
        if (!file_exists($basePath)) {
            mkdir($basePath, 0777, true);
        }
        $basePath .= '/' . date('Y');
        if (!file_exists($basePath)) {
            mkdir($basePath, 0777, true);
        }
        $basePath .= '/' . date('m');
        if (!file_exists($basePath)) {
            mkdir($basePath, 0777, true);
        }
        $basePath .= '/' . date('d');
        if (!file_exists($basePath)) {
            mkdir($basePath, 0777, true);
        }

        return $basePath;
    }

    private function removeFileExits($name)
    {
        $basePath = $this->getBasePath();

        try {
            unlink($basePath . ($this->$name));
        } catch (\Exception $e) {
            Log::debug($basePath . ($this->$name));
        }

        $this->removeThumbs($name, $basePath);
    }

    private function removeThumbs($name, $basePath)
    {
        $names = explode('/', $this->$name);
        $fileName = array_pop($names);
        $fileNameNoTail = explode('.', $fileName)[0];

        if (isset($this->thumbImage[$name]) && isset($fileNameNoTail)) {
            foreach (glob($basePath . implode('/', $names) . '*') as $folder) {
                $this->scanAndDeleteFile($folder, $fileNameNoTail);
            }
        }
    }

    private function scanAndDeleteFile($dir, $fileName)
    {
        if (is_dir($dir)) {
            foreach (glob($dir . '/*') as $file) {
                try {
                    if (is_file($file)) {
                        if (strpos($file, $fileName) !== false) {
                            unlink($file);
                        }
                    } else {
                        $this->scanAndDeleteFile($file, $fileName);
                    }
                } catch (\Exception $exception) {
                    logger($exception);
                }
            }
        } else {
            if (strpos($dir . '', $fileName) !== false) {
                unlink($dir . '');
            }
        }
    }

    public function getModelUploadClass()
    {
        return method_exists($this, 'modelUploader') ? $this->modelUploader() : $this->provideUploader();
    }

    public function provideUploader($filter = null)
    {
        if ($filter === null) {
            $filter = config('uploader.namespace', 'App\\ModelUploader\\') . class_basename($this) . 'Uploader';
        }

        return $filter;
    }

    public function getThumbPath($field, $sizes)
    {
        $img = $this->$field;
        $sizeImage = '_' . implode('_', $sizes) . '.';
        $imgThumbs = str_replace('.', $sizeImage, $img);

        return config('app.asset_url') . ("/storage{$imgThumbs}");
    }

    public function getImage($field)
    {
        return config('app.asset_url') . ("/storage{$this->$field}");
    }
}
