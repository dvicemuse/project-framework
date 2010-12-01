<?php

	class Encryption extends Framework
	{
		/*
		 * Encrypt a string
		 */
		function encrypt_string($text)
		{
			return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->config->config->encryption_key, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
		}

		/*
		 * Decrypt a string
		 */
		function decrypt_string($text)
		{
			return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->config->encryption_key, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
		}
	
	}

?>