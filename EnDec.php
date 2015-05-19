<?php
	function decrypt($encrypted){	
		return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($GLOBALS['sha1_password']), base64_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($GLOBALS['sha1_password']))), "\0");
	}
		
	function encrypt($data) {
		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($GLOBALS['sha1_password']), $data, MCRYPT_MODE_CBC, md5(md5($GLOBALS['sha1_password']))));
	}
?>