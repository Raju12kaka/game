<?php 
include_once dirname(dirname(__FILE__)).'/models/define.php';
$providerid = $_POST['providerId'];
if(in_array($providerid, array(134, 133))){
?>
	<input name="direct_csid" type="hidden" id="csid">
	<input name="flag" type="hidden" value="1">
	<script type='text/javascript' charset='utf-8' src='https://online-safest.com/pub/csid.js'></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	
<script>

setTimeout(function(){
var xyz = document.getElementById("csid");


	  $.post("csid_html.php",
	  {
	    name: xyz.value
	  },
	  function(data, status){
		  document.write(data);
		  break;
	  });
}, 3000);
	//window.stop();
</script>
<?php
}elseif(in_array($providerid, array(183, 184))){
?>
<input type="hidden" name="deviceNo" id="deviceNo" />
<script type="text/javascript" src="<?php echo WONDERLANDSCRIPTURL; ?>/pub/js/fb/tag.js?merNo=<?php echo $_POST['merno']; ?>&gatewayNo=<?php echo $_POST['gateway']; ?>uniqueId=<?php echo $_POST['uniqueid']; ?>"></script>
<script>
window.onload = function(){
	Fingerprint2.get(function(components) {
		var murmur = Fingerprint2.x64hash128(components.map(function(pair) {return pair.value}).join(), 31) ;
		// document.write(murmur);
		document.getElementById("deviceNo").value = murmur;
	});
}
</script>
<script>
setTimeout(function(){

var xyz = document.getElementById("deviceNo");

	  $.post("csid_html.php",
	  {
	    name: xyz.value
	  },
	  function(data, status){
		  document.write(data);
		  break;
	  });
	  
	//window.stop();
}, 3000);
</script>
<?php
}
?>