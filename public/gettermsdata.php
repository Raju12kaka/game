<?php
if($_POST)
{
	$request_array = file_get_contents('php://input');
	$responseData = $_REQUEST;

	// include_once dirname(dirname(__FILE__)).'/models/common.php';
	include_once dirname(dirname(__FILE__)).'/models/define.php';

	$playerId = $responseData['playerId'];
	$bonusCode = $responseData['bonusCode'];
  	
	if(!empty($playerId) && !empty($bonusCode)){
		$url = AVAILABLE_BONUS_TERMS_URL;

		$data = array();

		$data['player_id'] = $playerId;
		$data['bonusCode'] = $bonusCode;
		
		//$data = http_build_query($data);
		$curl = curl_init($url);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);// Show the output
		curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
		curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//Transmit datas by using POST

		$resp= curl_exec($curl);
		$err =  curl_error($curl);

		curl_close ($curl);

		$response = json_decode($resp,true);

		if($response['error']==0){
			$conditions = '';
			$terms = $response['terms'];
			if(!empty($terms)){
				foreach( $terms as $val ){
					$conditions .= '* ';
					$conditions .= $val['terms_and_conditions'];
				}
			}else{
				$conditions .= '';
			}
			echo $conditions;
		}		
	}

}
?>