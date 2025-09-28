<?php

define('SEND_EMAIL_TO_TECH', false);
define('TECH_EMAIL', 'xxx@xxx.xxx');

$env = getenv('APP_ENV');
if ($env == 'development'){
	define('VALIDATOR', 	"https://payments.royalcasino.local/bitcoin/request.php");
	define('PRIVATE_KEY', 	'EekohxeeQueil0');
	define('API_KEY', 	'C5yYIb/MHwys6gMut70vckXKKj/L/2Mur6Y1FweryCERD85Br+M0LvDJCv7/ncGv');
	define('API_SECRET_KEY', 	'10Cgj2ibAuCBry8AcXRpLFTn9IU7dcIPjmsA1my7LZdiTk6aAppsTA/ohXT0ZezI');
} elseif ($env == 'preproduction') {
    define('VALIDATOR',     "https://payments.cetroconcasino.com/bitcoin/request.php");
    define('PRIVATE_KEY',   'Owah6phuehufur');
	define('API_KEY', 	'C5yYIb/MHwys6gMut70vckXKKj/L/2Mur6Y1FweryCERD85Br+M0LvDJCv7/ncGv');
	define('API_SECRET_KEY', 	'10Cgj2ibAuCBry8AcXRpLFTn9IU7dcIPjmsA1my7LZdiTk6aAppsTA/ohXT0ZezI');
}else {
	define('VALIDATOR', 	"https://payments.royalcasinolounge.com/bitcoin/request.php");
	define('PRIVATE_KEY', 	'Owah6phuehufur');
	define('API_KEY', 	'C5yYIb/MHwys6gMut70vckXKKj/L/2Mur6Y1FweryCERD85Br+M0LvDJCv7/ncGv');
	define('API_SECRET_KEY', 	'10Cgj2ibAuCBry8AcXRpLFTn9IU7dcIPjmsA1my7LZdiTk6aAppsTA/ohXT0ZezI');
}

$_ARR_TEST_CARDS = array();
$_ARR_TEST_CARDS[100] = array('41111111111111111'); // VISA
$_ARR_TEST_CARDS[101] = array('51111111111111111'); // MC

define('CARD_FIRST_BLOCK', 6);
define('CARD_MIDDLE_BLOCK', 6);
define('CARD_LAST_BLOCK', 4);

?>
