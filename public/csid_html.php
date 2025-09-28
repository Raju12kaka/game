<?php 
// echo (json_encode($_POST));
$file = fopen("csid_value.txt","w");
fwrite($file,$_POST['name']);
fclose($file);

// session_start();
// $_SESSION['directcsid'] = $_POST['name']; 
//echo (json_encode($_POST));
?>