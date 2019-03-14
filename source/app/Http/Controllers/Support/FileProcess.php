<?php

namespace App\Http\Support;

use Closure;
use Storage;

class FileProcess
{
    public static function upload($file_name, $file_path)
    {
        $disk_public = \Storage::disk('upload');
        try {
            if ($file_path && $file_name) {
                return $disk_public->put($file_name, fopen($file_path, 'r'));
            } else {
                return false;
            }
        } catch (\Illuminate\Exception $ex) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            return false;
        }
    }

    public static function remove($file_name)
    {
        $disk_public = \Storage::disk('upload');
        try {
            if ($disk_public->exists($file_name)) {
                $file = $disk_public->delete($file_name);
                return $file;
            } else {
                return false;
            }
        } catch (\Illuminate\Exception $ex) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            return false;
        }
    }
}
