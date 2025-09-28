<?php 

	define('PROVIDER_ID', 110);
	define('PROVIDER_NAME', 'ecorepay');
	
	$_REQUEST_ID = $_GET['i'];
		
	if ($_REQUEST_ID){
	
            include_once dirname(dirname(dirname(__FILE__))).'/private/common.php';
            
            $_PARAMS = array();
            $_PARAMS = get_params($_REQUEST_ID);
                                    
            $_COMPANY = 'HALLMARK CASINO';
            
            if (isset($_GET['l'])) {
                    $_PARAMS['language'] = $_GET['l'];
            }
            
            if (!$_PARAMS['language']) {
            	$_PARAMS['language'] = 'en';
            }
               
            include_once dirname(dirname(dirname(__FILE__))).'/private/'.PROVIDER_NAME.'/langs.php';
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
	            <table cellpadding="0" cellspacing="0" class="Table100">
	                <tr>
	                    <td>
	                        <table cellpadding="0" cellspacing="0" class="Table100 barGray" style="background: #DDD; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px solid #999; ">
	                            <tr>
	                                <td class="TD100">
	                                </td>
	                            </tr>
	                        </table>
	                        
	                        <table cellpadding="0" cellspacing="0" class="Table100 barWhite" style="border: 1px solid #999">
	                            <tr>
	                                <td class="TD100" colspan="2" align="middle">
										<div>
											<?php 
											
											$percentage_width = '';
											if (is_wap($_PARAMS['web_id'])){
												$percentage_width  = 'width="50%"';
											} else {
												$percentage_width  = 'width="50%"';
											}
											
											switch($_PARAMS['payment_method_id']){
												case 100: ?><img src="img/visa.jpg" <?php echo $percentage_width; ?>><?php break;
												case 101: ?><img src="img/mastercard.jpg" <?php echo $percentage_width; ?>><?php break;
											}
											
											?>
										</div>                                 
	                                </td>
	                            </tr>
	                        </table>
	
	                        <table cellpadding="0" cellspacing="0" class="Table100 barGray" style="background: #DDD; border-left: 1px solid #999; border-right: 1px solid #999; ">
	                            <tr>
	                                <td align="left">
	                                    <span class="crumb"><span class='selectedcrumb' style='color:#333;'></span>
	                                </td>
	                            </tr>
	                        </table>
	                    </td>
	                </tr>
	            </table>

    
	<?php
	$bar_color  = '';
	if (!is_wap($_PARAMS['web_id'])){
		$bar_color  = "background:'#F1F0D3'; ";
	}
	?>
	
	<div class="content" style="<?php echo $bar_color;?> border: 1px solid #999; padding:0px;">
		<iframe src="form.php?i=<?php echo $_REQUEST_ID; ?>" style="border: 1px; padding:0px; width:100%; height:300px;"></iframe>
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
