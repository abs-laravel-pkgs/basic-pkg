<?php

namespace Abs\BasicPkg\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Container\Container;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Swift_Mailer;
use Swift_SmtpTransport;

class ConfigurableMail extends Mailable {
	use Queueable, SerializesModels;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct() {
	}

	public function send(MailerContract $mailer) {
		$host = $this->configs['host'];
		$port = $this->configs['port'];
		$security = $this->configs['encryption'];

		$transport = new Swift_SmtpTransport($host, $port, $security);

		$transport->setUsername($this->configs['username']);
		$transport->setPassword($this->configs['password']);
		$mailer->setSwiftMailer(new Swift_Mailer($transport));

		Container::getInstance()->call([$this, 'build']);
		$mailer->send($this->buildView(), $this->buildViewData(), function ($message) {
			$this->buildFrom($message)
				->buildRecipients($message)
				->buildSubject($message)
				->buildAttachments($message)
				->runCallbacks($message);
		});
	}
}
