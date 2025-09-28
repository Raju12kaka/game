<?php 

include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
$dir = "proofs/";

$get_time = time();
$rand = rand(1000000,9999999);
$ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
$image_name= $get_time.$rand.'.'.$ext;

try{
move_uploaded_file($_FILES["image"]["tmp_name"], $dir.$image_name);
}catch(exception $e){
	print_r($e);exit;
}
echo $image_name;

?>