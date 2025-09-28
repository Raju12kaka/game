<?php

class iCanPay {

    private $_API_URL;
    private $_3DSv_API_URL;
    private $_PreAuth_API_URL;
    private $_BC_API_URL;
    private $_SECRET_KEY;
    private $_PARAMS;
    private $_API_TYPE;

    function __construct($secretKey, $params = array(), $type = 'API') {

        $phpVersion = phpversion();
        if ($phpVersion < 7 && ($this->_mcrypt_exists = function_exists('mcrypt_encrypt')) === false) {
            //die('The Encrypt library requires the Mcrypt extension.');
        }

        $this->_PARAMS = $params;
        $this->_SECRET_KEY = $secretKey;
        $this->_API_URL = 'https://pay.icanpay.cn.com/pay/authorize_payment';
        $this->_3DSv_API_URL = 'https://pay.icanpay.cn.com/pay/authorize3dsv_payment';
        $this->_3DS_API_URL = 'https://pay.icanpay.cn.com/pay/authorize3ds';
        $this->_PreAuth_API_URL = 'https://pay.icanpay.cn.com/pay/pre_authorize';
        $this->_BC_API_URL = 'https://pay.icanpay.cn.com/pay/authorizebc_payment';
        $this->_API_TYPE = $type;

        $this->validatePayload();
    }

    public function payment() {
        $payload = array(
            "ccn" => $this->_PARAMS['ccn'],
            "expire" => $this->_PARAMS['exp_month'] . '/' . $this->_PARAMS['exp_year'],
            "cvc" => $this->_PARAMS['cvc_code'],
            "firstname" => $this->_PARAMS['firstname'],
            "lastname" => $this->_PARAMS['lastname']
        );

        $phpVersion = phpversion();
        if ($phpVersion >= 7) {
            $mode = '_PHP7';
            $encripted_card_info = $this->encryptForPhp7($payload); //Encript data for PHP version >= 7
        } else {
            define('IV_SIZE', mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));
            $mode = '';
            $encripted_card_info = $this->encrypt($payload);
        }

        $this->_PARAMS['card_info'] = $encripted_card_info;
        $this->_PARAMS['state'] = strlen($this->_PARAMS['state']) > 2 ? substr($this->_PARAMS['state'], 0, 2) : $this->_PARAMS['state'];
        $this->_PARAMS['customerip'] = $_SERVER['REMOTE_ADDR'];
        $transaction_hash = $this->_PARAMS['transaction_hash'];

        unset($this->_PARAMS['ccn']);
        unset($this->_PARAMS['cvc_code']);
        //unset($this->_PARAMS['firstname']);
        //unset($this->_PARAMS['lastname']);
        unset($this->_PARAMS['exp_year']);
        unset($this->_PARAMS['exp_month']);
        unset($this->_PARAMS['transaction_hash']);
        if ($this->_API_TYPE == '3DSV') {
            $this->_PARAMS['success_url'] = urlencode($this->_PARAMS['success_url']);
            $this->_PARAMS['fail_url'] = urlencode($this->_PARAMS['fail_url']);
            $this->_PARAMS['notify_url'] = urlencode($this->_PARAMS['notify_url']);
        }
        if ($this->_API_TYPE == '3DS') {
            $this->_PARAMS['success_url'] = urlencode($this->_PARAMS['success_url']);
            $this->_PARAMS['fail_url'] = urlencode($this->_PARAMS['fail_url']);
            $this->_PARAMS['notify_url'] = urlencode($this->_PARAMS['notify_url']);
        }

        $signature = "";
        ksort($this->_PARAMS);

        foreach ($this->_PARAMS as $key => $val) {
            if ($key != "signature" || $key != "card_info") {
                $signature .= $val;
            }
        }
        $signature = $signature . $this->_SECRET_KEY;
        $signature = strtolower(sha1($signature));
        $this->_PARAMS['signature'] = $signature;
        $this->_PARAMS['transaction_hash'] = $transaction_hash;

        if ($this->_API_TYPE == 'API') {
            $this->_PARAMS['tr_mode'] = 'API' . $mode;
            $response = $this->post_request();
            parse_str($response, $output);
            return json_encode($output);
        } elseif ($this->_API_TYPE == '3DSV') {
            $this->_PARAMS['tr_mode'] = 'API3DSv' . $mode;
            $requestDataJson = json_encode($this->_PARAMS);
            $logmessage = "Payment through iCanPay 3dsv redirect before encode: ".date("Y-m-d H:i:s")." with parameters: ".$requestDataJson."\n";
			file_put_contents(dirname(__DIR__).'/logs/payments_icanpay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
            $base64_encode = base64_encode($requestDataJson);
            $final_param = array('request' => $base64_encode);
            $url_args = http_build_query($final_param);
            $response = array('status' => 1, 'redirect_url' => $this->_3DSv_API_URL . '?' . $url_args);
            $logmessage = "Payment through iCanPay 3dsv redirect: ".date("Y-m-d H:i:s")." with parameters: ".json_encode($response)."\n";
			file_put_contents(dirname(__DIR__).'/logs/payments_icanpay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
            return json_encode($response);
        } elseif ($this->_API_TYPE == '3DS') {
            //$this->_PARAMS['tr_mode'] = 'API3DS' . $mode;
            $this->_API_URL = $this->_3DS_API_URL;
            unset($this->_PARAMS['card_info']);
            $url_args = http_build_query($this->_PARAMS);
            $response = $this->post_request();
            parse_str($response, $output);
            return json_encode($output);
        }elseif ($this->_API_TYPE == 'BCP') {
            $this->_PARAMS['tr_mode'] = 'APIBCP' . $mode;
            $this->_API_URL = $this->_BC_API_URL;
            $response = $this->post_request();
            parse_str($response, $output);
            if ($output['status'] == 1) {
                $response = array('status' => 1, 'redirect_url' => $output['redirect_url']);
            } else {
                $response = array('status' => 0, 'errorcode' => $output['errorcode'], 'errormessage' => $output['errormessage']);
            }
            return json_encode($response);
        } elseif ($this->_API_TYPE == 'PreAuth') {
            $this->_PARAMS['tr_mode'] = 'API' . $mode;
            $this->_API_URL = $this->_PreAuth_API_URL;
            $response = $this->post_request();
            parse_str($response, $output);
            return json_encode($output);
        }
    }

    private function encrypt($payload = array()) {
        $string = preg_replace("/[^A-Za-z0-9 ]/", '', $this->_SECRET_KEY);
        $sKey = substr($string, 0, 16);

        $iv = mcrypt_create_iv(IV_SIZE, MCRYPT_DEV_URANDOM);
        $crypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sKey, $this->array_implode_with_keys($payload), MCRYPT_MODE_CBC, $iv);
        $combo = $iv . $crypt;
        $encryptdata = base64_encode($iv . $crypt);

        return $encryptdata;
    }

    private function encryptForPhp7($payload = array()) {
        $string = preg_replace("/[^A-Za-z0-9 ]/", '', $this->_SECRET_KEY);
        $encryption_key = substr($string, 0, 16);
        // Generate an initialization vector
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        // Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
        $encrypted = openssl_encrypt($this->array_implode_with_keys($payload), 'aes-256-cbc', $encryption_key, 0, $iv);
        // The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
        return base64_encode($encrypted . '::' . $iv);
    }

    private function validatePayload() {
        $payload = $this->_PARAMS;
        if (!is_array($payload)) {
            //die('Data to be encripted must be in array format');
        }
        if ($this->_API_TYPE == 'API') {
            $required_parameter = array('authenticate_id', 'authenticate_pw', 'orderid', 'transaction_type', 'amount', 'currency', 'ccn', 'exp_month', 'exp_year', 'cvc_code', 'firstname', 'lastname', 'email', 'street', 'city', 'zip', 'state', 'country', 'phone', 'transaction_hash');
        } elseif (($this->_API_TYPE == '3DSV') || ($this->_API_TYPE == 'BCP') || ($this->_API_TYPE == '3DS')) {
            $required_parameter = array('authenticate_id', 'authenticate_pw', 'orderid', 'transaction_type', 'amount', 'currency', 'ccn', 'exp_month', 'exp_year', 'cvc_code', 'firstname', 'lastname', 'email', 'street', 'city', 'zip', 'state', 'country', 'phone', 'dob', 'success_url', 'fail_url', 'notify_url', 'transaction_hash');
        }

        foreach ($required_parameter as $key) {
            if (empty($payload[$key])) {
                //die($key . ' must have a value');
            }
        }
        if ($payload['ccn']) {
            $ccn = preg_replace('/[^0-9]/', '', $payload['ccn']);
            if ((strlen($ccn) < 13) || (strlen($ccn) > 16)) {
                //die($payload['ccn'] . ' is invalid card');
            }
        }
        if ($payload['cvc_code']) {
            $cvc_code = preg_replace('/[^0-9]/', '', $payload['cvc_code']);
            if ((strlen($cvc_code) < 3) || (strlen($cvc_code) > 4)) {
                //die($payload['cvc_code'] . ' is invalid cvc code');
            }
        }

        if (strlen($payload['country']) != 3) {
            //die($payload['country'] . ' is invalid country code');
        }

        if ($this->validDate($payload['exp_year'], $payload['exp_month']) == FALSE) {
            //die('Expiry date must be valid');
        }
        return TRUE;
    }

    private function validDate($year, $month) {
        $year = '20' . $year;
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        if (!preg_match('/^20\d\d$/', $year)) {
            return FALSE;
        }
        if (!preg_match('/^(0[1-9]|1[0-2])$/', $month)) {
            return FALSE;
        }
        // past date
        if ($year < date('Y') || $year == date('Y') && $month < date('m')) {
            return FALSE;
        }
        return TRUE;
    }

    private function array_implode_with_keys($array) {
        $return = '';
        if (count($array) > 0) {
            foreach ($array as $key => $value) {
                $return .= $key . '||' . $value . '__';
            }
            $return = substr($return, 0, strlen($return) - 2);
        }
        return $return;
    }

    private function post_request() {
        $data_stream = http_build_query($this->_PARAMS);
		$logmessage = "Payment through iCanPay: ".date("Y-m-d H:i:s")." with parameters: ".$data_stream."\n";
		file_put_contents(dirname(__DIR__).'/logs/payments_icanpay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_stream);
        curl_setopt($ch, CURLOPT_URL, $this->_API_URL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result_str = curl_exec($ch);
        if (curl_errno($ch) != 0) {
            $result_str = 'curl_error=' . curl_errno($ch) . '&status=0';
        }
        curl_close($ch);
        return $result_str;
    }

}
