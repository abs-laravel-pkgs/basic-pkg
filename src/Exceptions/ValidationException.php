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
			$this->setMessage('Validation error.'.implode(', ',$allErrors));
			foreach ($allErrors as $error) {
				$this->setMessages($error);
			}
		}
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	public function setMessages($message) {
		$this->messages[] = $message;
	}

	public function getMessages() {
		return $this->messages;
	}

}
