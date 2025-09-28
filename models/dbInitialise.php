<?php 
$env = getenv('APP_ENV');
if ($env == 'devlopment'){
	define('MYSQL_HOST', 'localhost');
	define('MYSQL_DB', 'betting');
	define('MYSQL_USER', 'root');
	define('MYSQL_PASS', 'Root@Pass#321');
	define('HOST_HTTP', 'https://payments.akkhacasino.local');
	define('HOST_HTTP_WEB', 'http://akkhacasino.local:4200/home');
	define('HOST_HTTP_NOTIFY', 'https://payments.akkhacasino.local');
	define('STAGING',	true);
} elseif ($env == 'preproduction') {
	define('MYSQL_HOST', 'localhost');
	define('MYSQL_DB', 'casino_cetrocon');
	define('MYSQL_USER', 'cetrocon_admin');
	define('MYSQL_PASS', 'lDpxRgwD');
	define('HOST_HTTP', 'https://payments.cetroconcasino.com');
	define('HOST_HTTP_WEB', 'https://www.cetroconcasino.com/home');
	define('HOST_HTTP_NOTIFY', 'https://payments.cetroconcasino.com');
	define('STAGING',	false);
} else {
    define('MYSQL_HOST', 'localhost');
    define('MYSQL_DB', 'betting');
    define('MYSQL_USER', 'akkha_mgadmin');
    define('MYSQL_PASS', 'Xg^Qdd7L{4Rk8rh!');
    define('HOST_HTTP', 'https://payments.akkhacasino.com');
    define('HOST_HTTP_WEB', 'http://akkhacasino.com/home');
    define('HOST_HTTP_NOTIFY', 'https://payments.akkhacasino.com');
    define('STAGING',       false);
}
