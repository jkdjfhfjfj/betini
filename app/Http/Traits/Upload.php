<?php

namespace App\Http\Traits;

use Intervention\Image\ImageManagerStatic as Image;

trait Upload
{
    public function makeDirectory($path)
    {
        if (file_exists($path)) return true;
        return mkdir($path, 0755, true);
    }

    public function removeFile($path)
    {
        return file_exists($path) && is_file($path) ? @unlink($path) : false;
    }

    public function uploadImage($file, $location, $size = null, $old = null, $thumb = null, $filename = null)
    {
        if (!$file || !method_exists($file, 'getRealPath')) {
            throw new \InvalidArgumentException('A valid image upload is required.');
        }

        $location = rtrim(base_path(trim($location, '/\\')), '/\\');
        $path = $this->makeDirectory($location);
        if (!$path) throw new \RuntimeException('Upload directory could not be created.');

        if ($filename == null) {
            $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $filename = \Illuminate\Support\Str::random(32) . '.' . $extension;
        }

        $image = Image::make($file->getRealPath());

        if (!empty($size)) {
            $size = explode('x', strtolower($size));
            $image->resize($size[0], $size[1]);
        }
        $image->save($location . '/' . $filename, 90);

        if (!empty($thumb)) {
            $thumb = explode('x', $thumb);
            Image::make($file->getRealPath())->resize($thumb[0], $thumb[1])->save($location . '/thumb_' . $filename, 90);
        }

        // Keep the previous image until the replacement and its thumbnail are safely written.
        if (!empty($old) && $old !== $filename) {
            $this->removeFile($location . '/' . $old);
            $this->removeFile($location . '/thumb_' . $old);
        }

        return $filename;
    }


}

