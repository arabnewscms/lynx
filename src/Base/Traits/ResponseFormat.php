<?php
namespace Lynx\Base\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseFormat
{
	protected $meta;
	protected $Jsondata;
	protected $errors = [];
	protected $status = 200;
	protected $message = 'successfully data';

	/**
	 * final result after set methods
	 * @return Illuminate\Http\JsonResponse
	 */
	public function response(): JsonResponse
	{
		if ($this->message == 'successfully data') {
			$this->message = __('lynx.successfully');
		}
		return response()->json(
			[
				'status' => $this->status != 200 ? false : true,
				'message' => $this->message,
				'result' => $this->Jsondata,
				'meta' => $this->meta,
				'errors' => $this->errors,
				'errorCode' => $this->status,
			],
			$this->status, [
				'Content-Type' => 'application/json',
				'Charset' => 'utf-8',
			], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		);
	}

	public function status($status)
	{
		$this->status = $status;
		return $this;
	}

	public function data($result)
	{
		$this->Jsondata = $result;
		return $this;
	}

	public function message($message)
	{
		$this->message = $message;
		return $this;
	}

	public function errors($errors)
	{
		$this->errors = $errors;
		return $this;
	}

	public function meta($meta)
	{
		$this->meta = $meta;
		return $this;
	}
}
