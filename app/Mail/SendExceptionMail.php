<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendExceptionMail extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	private $content;
	private $css;

	public function __construct($content)
	{
		$this->content = $content;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->subject('New Exception! ( ' . env('APP_NAME') . ' )')->from(env('EMAIL_FROM'), env('APP_NAME'))->view('mails.exception-mail', [
			'content' => $this->content
		]);
	}
}
