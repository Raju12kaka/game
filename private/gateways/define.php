<?php

define('SEND_EMAIL_TO_TECH', false);
define('TECH_EMAIL', 'xxx@xxx.xxx');

$env = getenv('APP_ENV');
if ($env == 'development'){
	define('VALIDATOR', 	"https://payments.royalcasino.local/gateways/request.php");
	define('PRIVATE_KEY', 	'EekohxeeQueil0');
} elseif ($env == 'preproduction') {
    define('VALIDATOR',     "https://payments.cetroconcasino.com/gateways/request.php");
    define('PRIVATE_KEY',   'Owah6phuehufur');
}else {
	define('VALIDATOR', 	"https://payments.royalcasinolounge.com/gateways/request.php");
	define('PRIVATE_KEY', 	'Owah6phuehufur');
}

$_ARR_TEST_CARDS = array();
$_ARR_TEST_CARDS[100] = array('41111111111111111'); // VISA
$_ARR_TEST_CARDS[101] = array('51111111111111111'); // MC

define('CARD_FIRST_BLOCK', 6);
define('CARD_MIDDLE_BLOCK', 6);
define('CARD_LAST_BLOCK', 4);

?>
