<?php
    /**  Log message start **/
  	$documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
  	array_pop($documentroot);
  	array_push($documentroot, 'logs');
  	$root_path = implode('/', $documentroot);

  
  
  	/**  Log message end **/
if(isset($_GET))
{
	$getdata = $_GET;
	file_put_contents($root_path.'/payments_totalpay3ds.log', date('Y-m-d H:i:s')."TOTALPAY3DS::get ::".json_encode($getdata).PHP_EOL , FILE_APPEND | LOCK_EX);

}

if(isset($_POST))
{
	$postdata = $_POST;
	file_put_contents($root_path.'/payments_totalpay3ds.log', date('Y-m-d H:i:s')."TOTALPAY3DS::post ::".json_encode($postdata).PHP_EOL , FILE_APPEND | LOCK_EX);

}

 ?>