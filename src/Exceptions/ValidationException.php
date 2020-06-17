<?php

namespace Abs\BasicPkg\Exceptions;

use Exception;
use Illuminate\Validation\Validator;

class ValidationException extends Exception {

	private $messages = [];

	public function __construct(Validator $validator = null) {
		if ($validator) {
			$allErrors = $validator->errors()
				->all();
			foreach ($allErrors as $error) {
				$this->setMessage($error);
			}
		}
	}

	public function setMessage($message) {
		$this->messages[] = $message;
	}

	public function getMessages() {
		return $this->messages;
	}

}
