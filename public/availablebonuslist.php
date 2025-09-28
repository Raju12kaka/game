<?php
if($_POST)
{
	$request_array = file_get_contents('php://input');
	$responseData = $_REQUEST;

	include_once dirname(dirname(__FILE__)).'/models/common.php';
	include_once dirname(dirname(__FILE__)).'/models/define.php';


	$playerId = $responseData['playerId'];
	$selected_amount = $responseData['selected_amount'];
  	
	if($responseData['playerId']){
		$url = AVAILABLE_BONUS_REDEEM_URL;
		// $url = 'https://adservices.akkhacasino.local/BonusExngCntr/getavailableBonusList';

		$data = array();

		$data['player_id'] = $playerId;
		
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
			if($response['bonusesCount'] >0){
				$AvailbonusDetails = $response['bonusSetDetails'];
				foreach($AvailbonusDetails as $key => $vl){
					if($vl['minimum_deposit_amount'] && ($selected_amount >= $vl['minimum_deposit_amount'])){
						$listofavail[$key]['bonusid'] = $vl['bonuses_id'];
						$listofavail[$key]['name'] = $vl['name'];
						$listofavail[$key]['min_amount'] = $vl['minimum_deposit_amount'];
						$listofavail[$key]['coupon_code'] = $vl['coupon_code'];
					}
				}
			}
		}

		if(!empty($listofavail)){
			$opt =  '<option value="0"> Select Available Option </option>';
			foreach( $listofavail as $val )
			{
				$bonusid = $val['bonusid'];
				$ccode = $val['coupon_code'];
				$redamount = $val['min_amount'];

				$opt.= '<option value="'.$ccode.'">'.$ccode.'</option>';
				// $opt.= '<option value="'.$bonusid.'">'.$ccode.' - '.$redamount.'</option>';
			}
		}else{
			// $opt.= '<option value="0"> Select Available Option </option>';
			$opt.= '';
		}

		echo $opt; 		
	}

}
?>