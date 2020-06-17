<?php

namespace Abs\BasicPkg\Classes;

use Abs\BasicPkg\Exceptions\UserFriendlyException;
use Abs\BasicPkg\Exceptions\ValidationException;
use Response;

class ApiResponse {

	public $success = true;
	private $messages = array();
	private $errors = array();
	private $data = array();
	private $httpStatus = 200;
	private $lineNo = null;
	private $file = null;

	function __construct(array $param = []) {
		if (count($param) > 0) {
			foreach ($param as $key => $value) {
				$this->setData($key, $value);
			}
		}
	}

	public function setError($error) {
		if ($error instanceof UserFriendlyException || $error instanceof ValidationException) {
			foreach ($error->getMessages() as $message) {
				$this->errors[] = $message;
			}
		} else if (is_array($error)) {
			foreach ($error as $message) {
				$this->errors[] = $message;
			}
		} else if ($error instanceof Exception) {
			$this->errors[] = $error->getMessage();
		} else if (is_string($error)) {
			$this->errors[] = $error;
		} else {
			$this->errors[] = $error->getMessage();
		}

		if ($error instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
			$this->setHttpStatus($error->getStatusCode());
		}
		$this->success = false;
	}

	public function setMessage($message) {
		$this->messages[] = $message;
	}

	public function setStatus($status) {
		$this->success = $status;
	}

	public function setHttpStatus($status) {
		$this->httpStatus = $status;
	}

	public function setLineNo($lineNo) {
		$this->lineNo = $lineNo;
	}

	public function setFile($file) {
		$this->file = $file;
	}

	public function setData($name, $data) {
		$this->data[$name] = $data;
	}

	public function getData($name) {
		return array_key_exists($name, $this->data) ? $this->data[$name] : null;
	}

	public function setDataArray($dataArray) {
		if (is_array($dataArray)) {
			foreach ($dataArray as $key => $value) {
				$this->setData($key, $value);
			}
		} else {
			throw new Exception('Tried to set data array with a non-array value');
		}
	}

	public function getDataArray() {
		return $this->data;
	}

	public function deleteData($name) {
		if (isset($this->data[$name])) {
			unset($this->data[$name]);
		}
	}

	public function toArray() {
		$arr = array(
			'success' => $this->success,
			'messages' => $this->messages,
			'errors' => $this->errors,
		);

		if ($this->httpStatus == 500) {
			$arr = array_merge($arr, [
				'errors' => $this->errors,
				'line' => $this->lineNo,
				'file' => $this->file,
			]);
		}

		foreach ($this->data as $k => $data) {
			$arr[$k] = $data;
		}
		return $arr;
	}

	public function response() {
		return Response::json($this->toArray(), $this->httpStatus);
	}

	public static function success() {
		return Response::json(array('success' => true, 'errors' => array(), 'messages' => array()), 200);
	}

	public static function error($error, $status = 200) {
		return Response::json(array('success' => false, 'errors' => array($error), 'messages' => array()), $status);
	}

}
