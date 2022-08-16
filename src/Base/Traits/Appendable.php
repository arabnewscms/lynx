<?php
namespace Lynx\Base\Traits;
use Illuminate\Validation\ValidationException;

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
		$columns   = array_merge($this->append(), $filter);
		$fillables = (new $this->entity)->getFillable();

		// delete ID from array
		if (count($fillables) > 0 && $fillables[0] == 'id') {
			unset($fillables[0]);
		}

		// check in loop now and throw validation exception
		foreach (array_keys($columns) as $column) {
			if (!in_array($column, array_values($fillables))) {
				// delete Uploaded File
				$this->deleteIntoSelfFileWhenFailed($columns);
				throw ValidationException::withMessages([
						$column => 'This column not found in entity or model class fillable',
					]);

			}
		}
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