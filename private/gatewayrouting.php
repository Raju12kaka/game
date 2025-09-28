<?php 
include_once('common.php');

function check_success_transaction($cardNo, $cardtype){

	$db = connect_db();
	if (!$db || !$cardNo){
		return false;
	}
	
	$encryptcard = encrypt_card($cardNo, 'encode');
	
	$count = hasonesuccess_transaction($encryptcard);
	
	if($count->cnt > 0){
		if($cardtype == 'Visa'){
			return 11;
		}else{
			return 11;
		}
    }else{
    	if($cardtype == 'Visa'){
			return 10;
		}else{
			return 11;
		}
    }	

}

?>
