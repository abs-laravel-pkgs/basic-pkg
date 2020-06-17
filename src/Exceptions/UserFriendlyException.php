<?php

namespace Abs\BasicPkg\Exceptions;

use Exception;

class UserFriendlyException extends Exception {

	private $messages = [];

	public function __construct($messages = null) {
		if (!is_null($messages)) {
			$this->setMessage($messages);
		}
	}

	public function setMessage($messages) {
		if (is_array($messages)) {
			foreach ($messages as $message) {
				$this->messages[] = $message;
			}
		} else if (is_string($messages)) {
			$this->messages[] = $messages;
		}
	}

	public function getMessages() {
		return $this->messages;
	}

}
