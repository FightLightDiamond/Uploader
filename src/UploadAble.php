<?php
/**
 * Created by PhpStorm.
 * User: cuongpm
 * Date: 6/19/19
 * Time: 10:20 AM
 */

namespace Uploader;


use Illuminate\Support\Facades\Log;
use Uploader\Facades\FormatFa;
use Uploader\Facades\UploadFa;

trait UploadAble
{
    public function uploader($input)
    {
        $uploader = $this->getModelUploadClass();
        $u = new $uploader($this, $input);

        return $u->handle();
    }

    public function uploads($input)
    {
        if (isset($this->fileUpload)) {
            foreach ($this->fileUpload as $name => $type) {
                if (isset($input[$name])) {
                    $input = $this->doing($input, $name, $type);
                }
            }
        }

        return $input;
    }

    private function doing($input, $name, $type)
    {
        if (is_array($input[$name])) {
            $input = $this->multi($input, $name, $type);
        } else {
            if (is_file($input[$name])) {
                $input = $this->processUploads($input, $name, $type);
                $this->removeFileExits($name);
            } else {
                unset($input[$name]);
            }
        }

        return $input;
    }

    private function multi($input, $name, $type)
    {
        $folder = '';

        if (isset($this->pathUpload)) {
            $folder = $this->pathUpload[$name];
        }

        $link = $this->generatePath($folder, $type);
        $thumb = isset($this->thumbImage[$name]) ? $this->thumbImage[$name] : [];

        foreach ($input[$name] as $index => $value) {
            if (is_file($input[$name][$index])) {
                if ($type === 0) {
                    $input[$name][$index] = UploadFa::file($input[$name][$index], $link);
                } else {
                    $input[$name][$index] = UploadFa::images(
                        $input[$name][$index],
                        $link,
                        $thumb
                    );
                }
            } else {
                unset($input[$name]);
            }
        }

        return $input;
    }

    private function processUploads($input, $name, $key)
    {
        $folder = '';

        if (isset($this->pathUpload)) {
            $folder = $this->pathUpload[$name];
        }

        $link = $this->generatePath($folder);

        if ($key === 0) {
            $input[$name] = UploadFa::file($input[$name], $link);
        } else {
            $input[$name] = UploadFa::images(
                $input[$name],
                $link,
                isset($this->thumbImage[$name]) ? $this->thumbImage[$name] : []
            );
        }

        return $input;
    }

    private function generatePath($folder)
    {
        $basePath = config('filesystems.disks.public.root');

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
        $basePath = config('filesystems.disks.public.root');

        if (isset($this->$name) && $this->$name != '') {
            try {
                unlink($basePath . ($this->$name));
            } catch (\Exception $e) {
                Log::debug($basePath . ($this->$name));
            }
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

    public function uploadImport($file)
    {
        $newName = FormatFa::reFileName($file);

        $file->storeAs(
            $this->table, $newName
        );

        return storage_path('app/' . $this->table . '/' . $newName);
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
}