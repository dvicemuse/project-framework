<?php

	class Email extends Framework
	{
		/**
		 * Send an HTML formatted email
		 * @param string $to
		 * @param string $subject
		 * @param string $message
		 * @return bool
		 */
		public function mail($to, $subject, $message)
		{

			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			
			// Additional headers
			$headers .= "From: {$this->config()->mail->no_reply_name} <{$this->config()->mail->no_reply_address}> \r\n";

			// Send mail
			return mail($to, $subject, $message, $headers);
		}

	}

?>