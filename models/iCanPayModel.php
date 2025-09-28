<?php
class iCanPayModel {

    public function getSignature($clean_post, $sec_key) {
    	ksort($clean_post);
    	$signature = '';
    
    	foreach ($clean_post as $key => $value) {
    		if (in_array($key, array('signature', 'success_url', 'fail_url', 'notify_url'))) continue;
    		$signature .= $value;
    	}
    
    	$signature = $signature . $sec_key;
    	$signature = strtolower(sha1($signature));
    
    	return $signature;
    }
	
	public function get3DSSignature($clean_post, $sec_key) {
    	ksort($clean_post);
    	$signature = '';
    
    	foreach ($clean_post as $key => $value) {
    		if (in_array($key, array('signature'))) continue;
    		$signature .= $value;
    	}
    
    	$signature = $signature . $sec_key;
    	$signature = strtolower(sha1($signature));
    
    	return $signature;
    }
    
    public function getCardHash(&$data, $sec_key) {
    	$payload = array(
    		"ccn" => $data['ccn'],
    		"expire" => $data['exp_month'] . '/' . $data['exp_year'],
    		"cvc" => $data['cvc_code'],
    		"firstname" => $data['firstname'],
    		"lastname" => $data['lastname']
    	);
    
    	unset($data['ccn']);
    	unset($data['exp_month']);
    	unset($data['exp_year']);
    	unset($data['cvc_code']);
    	unset($data['firstname']);
    	unset($data['lastname']);
    
    	$string = preg_replace("/[^A-Za-z0-9 ]/", '', $sec_key);
    	$encryption_key = substr($string, 0, 16);
    
//     	// Generate an initialization vector
    	$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    	
//     	// Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
    	$encrypted = openssl_encrypt($this->array_implode_with_keys($payload), 'aes-256-cbc', $encryption_key, 0, $iv);
//     	// The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
//     	echo "encrypted string :: ";
        
    	
//     	$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
//     	$crypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encryption_key, $this->array_implode_with_keys($data), MCRYPT_MODE_CBC, $iv);
//     	$combo = $iv . $crypt;
//     	$encryptdata = base64_encode($iv . $crypt);
    	
    	return base64_encode($encrypted . '::' . $iv);
//     	return $encryptdata;
    }
    
    public function array_implode_with_keys($array) {
    	$return = '';
    	if (count($array) > 0) {
    		foreach ($array as $key => $value) {
    			$return .= $key . '||' . $value . '__';
    		}
    		$return = substr($return, 0, strlen($return) - 2);
    	}
    	return $return;
    }
}
