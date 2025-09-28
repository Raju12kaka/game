<?php

include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
  
  
  
  
  $documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
  array_pop($documentroot);
  array_push($documentroot, 'logs');
  $root_path = implode('/', $documentroot);
  
  $logmessage = date('Y-m-d H:i:s')." hmtsquare callback redirect response  :".json_encode($_GET)."\n";
  file_put_contents($root_path.'/payments_newhmtsquare.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  
  $payment_id = $_GET['payment_id'];
  $pay = explode("-",$payment_id);
  $pay_id = $pay[1];
  if($_GET['status']==1){
  	$_LOCATION_URL  = REDIRECTURL.'-success/'.$pay_id;
  }else{
  	$_LOCATION_URL  = REDIRECTURL.'-failed';
  }
?>

 <script language="javascript">
        window.parent.location = "<?php echo $_LOCATION_URL; ?>";
 </script>