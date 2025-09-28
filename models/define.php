<?php

define('SEND_EMAIL_TO_TECH', false);
define('TECH_EMAIL', 'xxx@xxx.xxx');
define('APPENV', getenv('APP_ENV'));

$env = getenv('APP_ENV');

if ($env == 'development'){
	define('VALIDATOR', 	"https://payments.akkhacasino.local/gateways/request.php");
	define('PRIVATE_KEY', 	'EekohxeeQueil0');
	define('CARD_ENCRYPT_KEY', 'xxfgtmnjidppbmyews@00910426#@$*');
	define('CRMURL',     "http://crm.akkhacasino.local/");
	define('NOTIFYURL',     "https://payments.akkhacasino.local/3dnotify.php");
	define('PAYMENTURL',     "https://payments.akkhacasino.local/");
	define('REDIRECTURL',     "http://akkhacasino.local:4200/profile/deposit");
	define('WEBREDIRECTURL',     "https://akkhacasino.local:4200/profile/deposit");
	define('CALLBACKURL',     "https://payments.akkhacasino.local");
    define('CALLCARDBLOCKURL',     "http://192.168.1.15/casinoservices/allcardsdata");
	define('WONDERLANDPAYMENTURL',   "https://pay.wonderlandpay.com/TestTPInterface");
	define('WONDERLANDSCRIPTURL',   "https://pay.wonderlandpay.com");
	define('CASINO_SITE_ID' , "204");
    define('CASINO_ACCESS_TOKEN' , "049c38a126393a464eb1");
	define('CASINO_SERVICES_URL' , "https://adservices.akkhacasino.local/");
	define('DEFAULT_CURRENCY', 'INR');
	define('BONUS_REDEEM_URL' , "https://adservices.akkhacasino.local/DepositsCntr/redeembonusfrompayments");
	define('AVAILABLE_BONUS_REDEEM_URL' , "https://adservices.akkhacasino.local/BonusExngCntr/getavailableBonusList");
	define('AVAILABLE_BONUS_TERMS_URL' , "https://adservices.akkhacasino.local/BonusExngCntr/getavailableBonusTerms");
} elseif ($env == 'preproduction') {
    define('VALIDATOR',     "https://payments.cetroconcasino.com/gateways/request.php");
    define('PRIVATE_KEY',   'Owah6phuehufur');
	define('CARD_ENCRYPT_KEY', 'xxfgtmnjidppbmyews@00910426#@$*');
	define('CRMURL',     "http://crm.cetroconcasino.com/");
	define('NOTIFYURL',     "https://payments.cetroconcasino.com/3dnotify.php");
	define('PAYMENTURL',     "https://payments.cetroconcasino.com/");
	define('REDIRECTURL',     "https://www.cetroconcasino.com/bank/finishDeposit/");
	define('CALLBACKURL',     "https://payments.cetroconcasino.com");
    define('CALLCARDBLOCKURL',     "http://192.168.1.15/casinoservices/allcardsdata");
	define('DEFAULT_CURRENCY', 'INR');
}else {
	 define('VALIDATOR',     "https://payments.akkhacasino.com/gateways/request.php");
    define('PRIVATE_KEY',   'Owah6phuehufur');
    define('CARD_ENCRYPT_KEY', 'xxfgtmnjidppbmyews@00910426#@$*');
    // define('CRMURL',     "http://staronelocal.com");
	define('CRMURL',     	"https://adservices.akkhacasino.com/");
    define('NOTIFYURL',     "https://payments.akkhacasino.com/3dnotify.php");
    define('PAYMENTURL',     "https://payments.akkhacasino.com/");
    define('REDIRECTURL',     "https://akkhacasino.com/profile/deposit");
	define('WEBREDIRECTURL',     "http://akkhacasino.com/profile/deposit/");
    define('CALLBACKURL',     "https://payments.akkhacasino.com");
    define('CALLCARDBLOCKURL', "https://casinoservices.avenuepay.com/allcardsdata");
    define('WONDERLANDPAYMENTURL',   "https://pay.wonderlandpay.com/TPInterface");
    define('WONDERLANDSCRIPTURL',   "https://pay.wonderlandpay.com");
	define('CASINO_SITE_ID' , "204");
    define('CASINO_ACCESS_TOKEN' , "049c38a126393a464eb1");
	define('CASINO_SERVICES_URL' , "https://services.akkhacasino.com/");
	define('DEFAULT_CURRENCY', 'INR');
	define('BONUS_REDEEM_URL' , "https://adservices.akkhacasino.com/DepositsCntr/redeembonusfrompayments");
	define('AVAILABLE_BONUS_REDEEM_URL' , "https://adservices.akkhacasino.com/BonusExngCntr/getavailableBonusList");
	define('AVAILABLE_BONUS_TERMS_URL' , "https://adservices.akkhacasino.com/BonusExngCntr/getavailableBonusTerms");
}

$_ARR_TEST_CARDS = array();
$_ARR_TEST_CARDS[100] = array('4111111111111111'); // VISA
$_ARR_TEST_CARDS[101] = array('5111111111111111'); // MC

$_TEST_CARDS_ARR = array();
$_TEST_CARDS_ARR[100] = '4111111111111111'; // VISA
$_TEST_CARDS_ARR[101] = '5111111111111111'; // MC

define('CARD_FIRST_BLOCK', 6);
define('CARD_MIDDLE_BLOCK', 6);
define('CARD_LAST_BLOCK', 4);

?>
