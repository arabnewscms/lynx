<?php
namespace Lynx\Base\Traits;

trait UploadFiles {

	public function uploadFile(string $request, string $path = 'default') {
		if (request()->hasFile($request)) {
			$file      = request()->file($request);
			$ext       = $file->getClientOriginalExtension();
			$full_path = $file->store($path, env('FILESYSTEM_DRIVER', 'public'));
			$hashname  = $file->hashName();
			return $full_path;
		} else {
			return false;
		}
	}
}