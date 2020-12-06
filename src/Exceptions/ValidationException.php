<?php

namespace Abs\BasicPkg\Exceptions;

use Exception;
use Illuminate\Validation\Validator;

class ValidationException extends Exception {

	private $messages = [];
	public $errors = [];

	public function __construct(Validator $validator = null) {
		if ($validator) {
			$allErrors = $validator->errors()
				->all();
			foreach ($allErrors as $error) {
				$this->setError($error);
			}
		}
	}

	public function setError($message) {
		$this->errors[] = $message;
	}

	public function getMessages() {
		return $this->messages;
	}

}
