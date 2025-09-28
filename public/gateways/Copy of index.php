<?php

define('PROVIDER_ID', 108);
define('PROVIDER_NAME', 'gateways');

$_REQUEST_ID = $_GET['i'];
$_PAYMNET_ID = isset($_GET['c']) ? $_GET['c'] : '';
$_VIRTUAL_PAYMENT = isset($_GET['vt']) ? $_GET['vt'] : '';
$billing = isset($_GET['billing']) ? $_GET['billing'] : '';
$gatewayListcss = empty($_PAYMNET_ID) ? '' : 'style="display:none"';
$gatewayListwidth = empty($_PAYMNET_ID) ? '57%' : '95%"';

if(!empty($_VIRTUAL_PAYMENT)){
    $gatewayListcss = 'style="display:none"';
    $gatewayListwidth = '92%';
}

if ($_REQUEST_ID){

include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';

$_PARAMS = array();
$_PARAMS = get_params($_REQUEST_ID);

//get list of continent based payment methods
$continent_methods = avilable_methods_continent($_PARAMS['country_id']);

//players_classes_id
$dat=get_player_all_details($_PARAMS['player_id']);

 /*Checking for Auditor*/
if($dat->players_classes_id == '19'){
	$checkplaybonus = check_plays_bonuses($_PARAMS['player_id']);
	if($checkplaybonus == 1){
		updateAuditorRiskLevel($_PARAMS['player_id'], 8);
 	}
}

$continent_methods = avilable_methods_continent_new($_PARAMS['country_id'],$dat->players_classes_id,$_PARAMS['web_id']);

list($continent_methods,$defaultmethod,$providerid)=explode('###',$continent_methods);

if (strpos($continent_methods, $defaultmethod) !== false) {
			$defaultmethod = $defaultmethod;
		}else{
			$explode_methods = explode(',', $continent_methods);
			$defaultmethod = $explode_methods[0];
		}

if(!$continent_methods){

    if ($_PARAMS['web_id']==11) { // Mobile

        echo " <div> <h3 style='color:#fff;'> You dont have any payment methods. Please contact administrator. </h3> </div>";
    }
    else {

        echo " <div> <h3>You dont have any payments methods. Please contact administrator.</h3>  </div>";
    }

    exit;
}

//echo " <h1> Hell HellO HELLO --  $continent_methods -- $defaultmethod -- $providerid </h1> ";


if(empty($_VIRTUAL_PAYMENT) && $_PARAMS['is_first_time'] == 0){
    //get default payment method
    $default_method = get_deault_payment_method($_PARAMS['web_id'], $_PARAMS['country_id'], $continent_methods);
    $default_method->payment_method_id=$defaultmethod;
    if($default_method){
         // Assigning binrules default method
         if($default_method->payment_method_id == 105){
         	$provider = 109;
         }else if($default_method->payment_method_id == 112){
         	$provider = 137;
		 }else if($default_method->payment_method_id == 121){
		 	$provider = 189;
		 }else{
		 	$provider = 108;
		 }
        // $provider = ($default_method->payment_method_id == 105) ? 109 : (($default_method->payment_method_id == 112) ? 137 : 108);
        update_payment_method($default_method->payment_method_id, $provider, $_PARAMS['id'], 1);
        $_PARAMS = get_params($_REQUEST_ID);
    }else{
        $active_methods = get_active_methods($_PARAMS['web_id'], $_PARAMS['country_id'], $continent_methods);
        // $provider = ($active_methods[0]['payment_method_id'] == 105) ? 109 : (($default_method->payment_method_id == 112) ? 137 : 101);
		if($$active_methods[0]['payment_method_id'] == 105){
         	$provider = 109;
         }else if($active_methods[0]['payment_method_id'] == 112){
         	$provider = 137;
		 }else if($active_methods[0]['payment_method_id'] == 121){
		 	$provider = 189;
		 }else{
		 	$provider = 101;
		 }
        update_payment_method($active_methods[0]['payment_method_id'], $provider, $_PARAMS['id'], 1);
        $_PARAMS = get_params($_REQUEST_ID);
    }
}

$_COMPANY = 'SLOTS7CASINO';

if (isset($_GET['l'])) {
    $_PARAMS['language'] = $_GET['l'];
}

if (!$_PARAMS['language']) {
    $_PARAMS['language'] = 'en';
}

include_once dirname(dirname(dirname(__FILE__))).'/models/langs.php';
//List of payment types
$payment_type = get_payment_types();
$payment_type = array_combine(range(1, count($payment_type)), array_values($payment_type));

//get activated payment methods to display
$active_methods = get_active_methods($_PARAMS['web_id'], $_PARAMS['country_id'], $continent_methods);

foreach($active_methods as $act){
    $ptypes[] = $act['payment_type'];
	if($act['payment_method_id'] == $_PARAMS['payment_method_id'])
		$currentPaymentType = $act['payment_type'];
}
$sortingArr = array_unique($ptypes);
foreach($sortingArr as $val){ // loop
    $result[] = $payment_type[$val]; // adding values
}
$paymenttypes = array_slice($payment_type, count($result));
$finalresult = array_merge_recursive($result, $paymenttypes);
// $payment_type = $finalresult;
$payment_type = $result;
//get payment type by paymentmethod
//$currentPaymentType = get_current_paymenttype($_PARAMS['payment_method_id']);
//$currentPaymentType->payment_type
?>

<link type="text/css" href="css/styles.css" rel="stylesheet"/>
<link type="text/css" href="css/messi.css" rel="stylesheet"/>
<link type="text/css" href="css/new_modules.css" rel="stylesheet"/>
<link type="text/css" href="css/jquery-ui.css" rel="stylesheet"/>
<!--<link type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.8.21/themes/base/jquery-ui.css" rel="stylesheet"/>-->
<script language="javascript" src="js/jquery.min.js"></script>
<script language="javascript" src="js/messi.min.js"></script>
<script language="javascript" src="js/jquery-ui.min.js"></script>
<script language="javascript" src="js/bootstrap.min.js"></script>

<script type="text/javascript">
    function loadIframe(type, method, provider, category){
        $.ajax({
            type:'post',
            url:'request.php',
            data:'cardtype='+type+'&method='+method+'&provider='+provider+'&change=1&id=<?php echo $_PARAMS['id']; ?>&loaded=<?php echo $_PARAMS['has_loaded']+1; ?>&category='+category,
            success:function(res){
                if(res == 'success'){
                    location.reload(true);
                }else{

                }
            },
            error:function(res){
                alert('Problem with loading form.');
            }
        });
    }
</script>
<!-- Payments iframe code for casino websites -->

<?php if (is_wap($_PARAMS['web_id'])){ ?>
    <style>



    </style>


<?php } else{  ?>

<?php } ?>

<style>
    /*.more.active {
        background: black;
    }*/
</style>
<script>
    $( function() {
        var icons = {
            header: "ui-icon-plusthick",
            activeHeader: "ui-icon-minusthick"
        };
        $( ".acordion" ).accordion({
            icons: icons
        });

    } );
</script>


<script>
    /*----Accordion script new----*/
    function expandAll() {
        $('#accordion .more').removeClass('ui-state-default')
            .addClass('ui-state-active')
            .removeClass('ui-corner-all')
            .addClass('ui-corner-top')
            .attr('aria-expanded', 'true')
            .attr('aria-selected', 'true')
            .attr('tabIndex', 0)
            .find('span.ui-icon')
            .removeClass('ui-icon-circle-plus')
            .addClass('ui-icon-circle-minus')

            .closest('.more').next('div')
            .show();
        $('#accordion .es').addClass('ui-accordion-content-active')
            .attr('aria-hidden','false')

        $('.expand').text('collapse all').unbind('click').bind('click', collapseAll);

        $('#accordion .more').bind('click.collapse', function() {
            collapseAll();
            $(this).click();
        });
    }
    function collapseAll() {
        $('#accordion .more').unbind('click.collapse');

        $('#accordion .more').removeClass('ui-state-active')
            .addClass('ui-state-default')
            .removeClass('ui-corner-top')
            .addClass('ui-corner-all')
            .attr('aria-expanded', 'false')
            .attr('aria-selected', 'false')
            .attr('tabIndex', -1)
            .find('span.ui-icon')
            .removeClass('ui-icon-circle-minus')
            .addClass('ui-icon-circle-plus')

            .closest('.more').next('div')
            .hide();
        $('#accordion .es').removeClass('ui-accordion-content-active')
            .attr('aria-hidden','true')

        $('.expand').text('expand all').unbind('click').bind('click', expandAll);

        $('#accordion').accordion('destroy').accordion();
    }

    $(document).ready(function() {
        setTimeout(function() {
            $(".expand").trigger('click');
        },10);
        $('.expand').click(expandAll);


        $('#accordion').accordion();
    });
    /*----Accordion script new end----*/





    $( function() {
        var icons = {
            header: "ui-icon-circle-plus",
            activeHeader: "ui-icon-circle-minus"
            /*  header: "ui-icon-triangle-1-s",
            activeHeader: "ui-icon-triangle-1-e"*/
        };
        $( ".acordion" ).accordion({
            icons: icons,


        });


    } );

</script>




<div id="deposit_main_div">



    <div class="deposit_leftdiv">

        <?php //if(in_array($a, $active_methods[0])){ echo "active"; }else{ echo "noactive"; } ?>
        <?php $a = 1; ?>
        <?php for($i=0;$i<count($payment_type);$i++){ ?>
            <?php if (!is_wap($_PARAMS['web_id'])){ ?>
                <div class="more <?php echo ($payment_type[$i]['id'] == $currentPaymentType->payment_type) ? 'active activedivid' : 'noactive' ?>"><h2><?php echo $payment_type[$i]['name'] ?></h2></div>
            <?php } ?>




            <?php foreach($active_methods as $active): ?>
                <?php if($active['payment_type'] == $payment_type[$i]['id']){ ?>
                    <div data-bound="<?php echo strtolower($active['name'])?>" data-id="<?php echo strtolower($active['payment_method_id'])?>" class="cuadro_metodos_boton_seleccion_metodo<?php echo ($_PARAMS['payment_method_id'] == $active['payment_method_id'] && ($_PARAMS['is_dummy'] == 0 || $payment_type[$i]['id'] == $_PARAMS['is_dummy'])) ? ' selected_method_btn' : ' unselected_method_btn' ?> featuvisacls <?php echo str_replace("/","",(str_replace(" ","",$payment_type[$i]['name']))) ?>" onclick="loadIframe('<?php echo strtolower($active['name'])?>', '<?php echo strtolower($active['payment_method_id'])?>', '<?php echo $active['payment_provider_id']?>', '<?php echo $payment_type[$i]['id'] ?>');" >
                        <img src="img-bank/<?php echo $active['processor_logo'] ?>" width="86"  />
                        <!--<div class="txt"><?php echo strtoupper($active['name'])?></div>-->
                        <?php if (is_wap($_PARAMS['web_id'])){ ?>
                            <!--<div class="deposit-btn"><a class="btnbig">Deposit</a></div>-->
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php endforeach; ?>



            <?php $a++; } ?>



    </div>


    <div class="visible" id="metodos_contenedor_metodos_de_pago">
        <div id="metolist_metodos_cuerpo" style="display:block;">


            <div class="acc_str">


                <?php if (!is_wap($_PARAMS['web_id']) && empty($_VIRTUAL_PAYMENT)){ ?>


                    <div class="text-metodo" style="text-align: center;height: 74px;display:none;">

                        <div align="center" ><img src="img/trian.gif" width="136px" height="35x"></div>
                        To make your deposit select your preferred method of payment.
                    </div>
                <?php } ?>

                <div <?php echo $gatewayListcss; ?> >

                    <a href="#" class="expand" style="color:white;font-size:1px;visibility:hidden;">expand all</a>






                </div></div>

            <?php if (is_wap($_PARAMS['web_id'])){ ?>
            <div style="width:100%;">
                <?php }else{ ?>
                <div style="width:100%;margin:auto;">
                    <?php } ?>
                    <?php
                    $styledisplay = '';
                    if (is_wap($_PARAMS['web_id']) && $_PARAMS['has_loaded'] == 0){
                        //$styledisplay = 'style="display:none;"';
                    }
                    ?>
                    <div class="contenido"<?php echo $styledisplay; ?>>
                        <?php if (is_wap($_PARAMS['web_id'])){ ?>
                        <div id="metolist_detalle_metodos" style="width:98%;margin-right:0px; margin-top:0px; min-height:500px;z-index:999;background: white;">
                            <?php }else{ ?>
                            <div id="metolist_detalle_metodos" style="width:98%;margin-right:0px; margin-top:15px;float:left;min-height:600px;z-index:999;background: white;/*border-radius:10px;border:7px solid #bbb;*/">
                                <?php } ?>
                                <div data-bound="visa">
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
                                                    <!-- <table cellpadding="0" cellspacing="0" class="Table100 barGray" style="background: #DDD; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px solid #999; ">
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

                                                    $provider_url = 'form.php?';
                                                    switch($_PARAMS['payment_method_id']){
                                                        case 105:
                                                            $provider_url = '../bitcoin/index.php?';
                                                            $provider_name = '/bitcoin/';
                                                            break;
                                                    }

                                                    ?>
											<img src="img/<?php echo $_PARAMS['image'] ?>" <?php echo $percentage_width; ?> <?php echo $ppercentage_height; ?>>
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
	                        </table>-->
                                                </td>
                                            </tr>
                                        </table>




                                        <div class="content" style="<?php echo $bar_color;?> /*border: 1px solid #999;*/ padding:0px;">
                                            <!----------title and card logo------>
                                            <div class="title1">
                                                <div class="title" style="width: 45%;float: left;">
                                                    <?php echo $_FORM_TEXTS['title2']; ?>
                                                    <span id="asterisc" class="rowColoured" style="font-size:13px; padding-left:20px;">* <?php echo $_FORM_TEXTS['required']; ?></span>
                                                    <br>
                                                </div>

                                                <div class="plogo" >

                                                    <div class="bimg">
                                                        <?php

                                                        $percentage_width = '';
                                                        if (is_wap($_PARAMS['web_id'])){
                                                            $percentage_width  = 'width="50%"';
															$inlinewidth = ($_PARAMS['payment_method_id'] == 112) ? '20' : '20';
                                                        } else {
                                                            $percentage_width  = 'width="50%"';
															$inlinewidth = ($_PARAMS['payment_method_id'] == 112) ? '20' : '20';
                                                        }

                                                        $provider_url = 'form.php?';
                                                        switch($_PARAMS['payment_method_id']){
                                                            case 105:
                                                                $provider_url = '../bitcoin/index.php?';
                                                                $provider_name = '/bitcoin/';
                                                                break;
                                                        }

                                                        if($_PARAMS['is_dummy'] == 2 && $_PARAMS['payment_method_id'] == 112){
                                                        ?>
                                                        <img src="img/qb-rt-img.png" <?php echo $percentage_width; ?> <?php echo $ppercentage_height; ?> class="rightimgcls">
                                                        <?php }else{  ?>
                                                        <img src="img/<?php echo $_PARAMS['image'] ?>" <?php echo $percentage_width; ?> <?php echo $ppercentage_height; ?> style="display: block;width: <?php echo $inlinewidth ?>%; float: right; margin-right:10px;">
                                                        <?php } ?>
                                                    </div>

                                                </div>
                                            </div>
                                            <!-----------end---------->
                                            <?php
                                            $percentage_width = '';
                                            if (is_wap($_PARAMS['web_id'])){
                                                $percentage_width  = 'width="50%"';
                                            } else {
                                                $percentage_width  = 'width="50%"';
                                            }

                                            $provider_url = 'form.php?';
                                            switch($_PARAMS['payment_method_id']){
                                                case 105:
                                                    $provider_url = '../bitcoin/index.php?';
                                                    $provider_name = '/bitcoin/';
                                                    break;
                                                case 108:
                                                    $provider_url = '../upaycard/index.php?';
                                                    $provider_name = '/upaycard/';
                                                    break;
                                                case 109:
                                                    $provider_url = '../skrill/index.php?';
                                                    $provider_name = '/skrill/';
                                                    break;
                                                case 110:
                                                    $provider_url = '../wirecard/index.php?';
                                                    $provider_name = '/wirecard/';
                                                    break;
                                                case 111:
                                                    $provider_url = '../cubits/index.php?';
                                                    $provider_name = '/cubits/';
                                                    break;
												case 112:
                                                    $provider_url = '../quickbits/index.php?';
                                                    $provider_name = '/quickbits/';
                                                    break;
												case 116:
													$provider_url = '../cardbit/index.php?';
													$provider_name = '/cardbit/';
													break;
												case 121:
													$provider_url = '../asiapay/index.php?';
													$provider_name = '/asiapay/';
													break;
												case 122:
													$provider_url = '../netcents/index.php?';
													$provider_name = '/netcents/';
													break;
												case 123:
													$provider_url = '../paytriot/index.php?';
													$provider_name = '/paytriot/';
													break;
                                            }


                                            $bar_color  = '';
                                            if (!is_wap($_PARAMS['web_id'])){
                                                $bar_color  = "background:'#F1F0D3'; ";
                                            }

                                            $c = '';
                                            if(isset($_GET['c'])){
                                                $c = "&c=".$_GET['c'];
                                            }
                                            $vt = '';
                                            if(isset($_GET['vt'])){
                                                $vt = "&vt=".$_GET['vt'];
                                            }
                                            $bill = '';
                                            if(isset($_GET['billing'])){
                                                $bill = "&bill=".$_GET['billing'];
                                            }
                                            ?>

										<?php echo $provider_url.'i='.$_REQUEST_ID; ?>
                                        <script type="text/javascript">
                                            $(document).ready(function(){
                                                $('#loadiframeurl').load('https://payments.slots7multilocal.com/gateways/form.php?i=d04d7af2cc2e05a5b3e00e171fa13445-dbe7cdcfb323d3d023c01a768d867cd495e05720', '', function(response, status, xhr) {
                                                    if (status == 'error') {
                                                        var msg = "Sorry but there was an error: ";
                                                        $(".content").html(msg + xhr.status + " " + xhr.statusText);
                                                    }
                                                });
                                            });
                                        </script> 
                                        <div id="loadiframeurl"></div>
										   
                                           <iframe class="qbdiframe" src="<?php echo $provider_url.'i='.$_REQUEST_ID; ?><?php echo $c; ?><?php echo $vt; ?><?php echo $bill; ?>" style="padding:0;border:none;display:block; width:100%;height:712px;"></iframe>
                                            
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $(".activedivid").click();
        });
    </script>
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
