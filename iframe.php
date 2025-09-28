<?php

define('PROVIDER_NAME', 'gateways');


if ($_REQUEST_ID){
	// print_r($_REQUEST_ID); die;
	include_once 'models/common.php';
				
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	
	// print_r($_PARAMS); die;
	
	if ($_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
		
		$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
			
			$_HOST_HTTP = HOST_HTTP;
			
			if (is_wap($_PARAMS['web_id'])){
				$iframe_width  = '100%';
				$iframe_height = ($_PARAMS['has_loaded'] == 0) ? '1000px' : '730px';
			} else {
				$iframe_width  = '100%';
				$iframe_height = '940px';
			}
			
			$provider_name = PROVIDER_NAME;
			
			$_RESULT['status'] = 'Success';
			$_RESULT['errorcode'] = '200';
			$_RESULT['src'] = $_HOST_HTTP.'/'.$provider_name.'/?i='.$_REQUEST_ID;
			$_RESULT['html']=<<<EOD
			<iframe id="idiframe" src="{$_HOST_HTTP}/{$provider_name}/?i={$_REQUEST_ID}" width="{$iframe_width}" height="{$iframe_height}"></iframe>
EOD;
	
	} else { // INVALID PARAMETERS
		$_RESULT['status'] = 'Error';
		$_RESULT['errorcode'] = 103;
		$_RESULT['html'] = 'Invalid parameters';
	}

	
} else { // NO REQUEST_ID
	$_RESULT['status'] = 'Error';
	$_RESULT['errorcode'] = 101;
	$_RESULT['html'] = 'No request id';
}
		
?>
