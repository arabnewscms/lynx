<?php
namespace Lynx\Base\Traits;

Trait Appendable {
	/**
	 * this method append data when store or update data
	 * @return array
	 */

	public

	function append():array{
		return [];
	}

	/**
	 * clean every request hasFile to prepare it manual in parent controller
	 * @return array
	 */
	public function allInputsWithoutFiles():array{
		$filter = [];
		foreach ($this->data as $key => $value) {
			if (!request()->hasFile($key)) {
				$filter[$key] = $value;
			}
		}

		// Check if Append Column not exist in fillable array from model
		$columns = array_merge($this->append(), $filter);

		return $columns;
	}

	/**
	 * this method detremine if stopped the proccess and has file uploaded
	 * do delete file untill not make a temp files or use space from server
	 * @return object
	 */
	public function deleteIntoSelfFileWhenFailed($columns):void {
		// delete Uploaded File
		foreach (array_keys($columns) as $checkFile) {
			if (request()->hasFile($checkFile)) {
				if (isset($this->append()[$checkFile]) && \Storage::exists($this->append()[$checkFile])) {
					\Storage::delete($this->append()[$checkFile]);
				}
			}
		}

	}

}
