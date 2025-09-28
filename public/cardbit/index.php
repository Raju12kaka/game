<?php 

	define('PROVIDER_ID', 182);
	define('PROVIDER_NAME', 'cardbit');
	
	$_REQUEST_ID = $_GET['i'];
		
	if ($_REQUEST_ID){
	
            include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
            
            $_PARAMS = array();
            $_PARAMS = get_params($_REQUEST_ID);
                                    
            $_COMPANY = 'Silvercoin CASINO';
            
            if (isset($_GET['l'])) {
                    $_PARAMS['language'] = $_GET['l'];
            }
            
            if (!$_PARAMS['language']) {
            	$_PARAMS['language'] = 'en';
            }
               
            include_once dirname(dirname(dirname(__FILE__))).'/models/langs.php';
        ?>
		  
	    <link type="text/css" href="css/styles.css" rel="stylesheet"/>
        <link type="text/css" href="css/messi.css" rel="stylesheet"/>
	    <script language="javascript" src="js/jquery.min.js"></script>
        <script language="javascript" src="js/messi.min.js"></script>
           
	    <script language="javascript">
        </script>
	            
	<noscript>
		<div class="tip">
			<b>Your browser does not support JavaScript or JavaScript is not currently enabled.</b><br />It is strongly recommended to enable JavaScript before continuing with your payment.
		</div>
	</noscript>
	    
	    
	<div>
	
	<form name="form_pay" method="POST" action="request.php" onSubmit="return checkform(this);">
	
	</div>
			
	        <div class="mainDiv">
	           

    
	<?php
	$bar_color  = '';
	if (!is_wap($_PARAMS['web_id'])){
		$bar_color  = "background:'#F1F0D3'; ";
		$width= "height:450px;";
	}else{
		$width= "height:360px;";
	}
	?>
	
	<div class="content" style="<?php echo $bar_color;?> border: 0px solid #999; padding:0px;">
		<iframe src="form.php?i=<?php echo $_REQUEST_ID; ?>" scrolling="no" style="border: 1px; padding:0px; width:100%; height:100% !important; <?php echo $width; ?>"></iframe>
	</div>

	</div>
		
	<?php
	} else { // NO REQUEST_ID
		$_RESULT['status'] = 'Error';
		$_RESULT['errorcode'] = 101;
		$_RESULT['html'] = 'No request IP';
	}
	
if (isset($_RESULT['status']) && $_RESULT['status'] == 'Error'){
	echo "<span style='font-family: Helvetica, Arial, Calibri, sans-serif;color: #333;font-size: 12px;'>{$_RESULT['html']}</span>";
}

?>
