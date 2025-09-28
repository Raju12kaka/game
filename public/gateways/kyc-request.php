 
<style>
	.kyc-details{color:#000;font-size:17px;text-align:center;line-height:24px;}
	
    .kyc-details img{width: 351px;}
	.kyc-details a {color:#d83e0f;text-decoration:none;}
	.kyc-other{text-align:center;}
	.kyc-other a{display: inline-block;
    background: #61d666;
    color: #000;
    padding: 10px 15px;
    text-decoration: none;
    text-transform: capitalize;
    font-weight: bold;
    border-radius: 6px;
    cursor: pointer;}
</style>
<div class="kyc-details">
    <!--<h1>KYC</h1>-->	
	<img src="images/kyc.jpg" alt="icon">
	<p>Please provde your kyc documents. We will increase your transaction limits for smooth transaction process. For any assistance please contact us on our chat support or email us at <a href="mailto:support@slots7casino.com">support@slots7casino.com</a></p>
</div>
<div class="kyc-other" >To pay with another payment option <a href="javascript:void(0);" onclick="loadIframe('quickbits', '112', '137', '2');">Click here</a> </div>
<script language="javascript" src="js/jquery.min.js"></script>
<script language="javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript">
    function loadIframe(type, method, provider, category){
      	
     window.top.postMessage(970 + '-' + 'iframe1', "*");	
        $.ajax({
            type:'post',
            url:'request.php',
            data:'cardtype='+type+'&method='+method+'&provider='+provider+'&change=1&id=<?php echo $_PARAMS['id']; ?>&loaded=<?php echo $_PARAMS['has_loaded']+1; ?>&category='+category,
            success:function(res){
                if(res == 'success'){
                    location.href='../gateways/?i=<?php echo $_REQUEST_ID;  ?>';
                }else{

                }
            },
            error:function(res){
                alert('Problem with loading form.');
            }
        });
    }
</script>
