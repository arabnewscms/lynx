<?php

namespace Lynx\Base\Traits;

use Illuminate\Support\Facades\Storage;

trait UploadFiles
{

    public function uploadFile(string $request, string $path = 'default')
    {
        if (request()->hasFile($request)) {
            $file = request()->file($request);

            if (env('FILESYSTEM_DISK') != 'public') {
                $full_path = Storage::disk(env('FILESYSTEM_DISK'))->put($path, $file);
            } else {
                // $ext       = $file->getClientOriginalExtension();
                $full_path = $file->store($path, env('FILESYSTEM_DISK'));
                // $hashname  = $file->hashName();
            }

            return $full_path;
        }

        return false;
    }
}
