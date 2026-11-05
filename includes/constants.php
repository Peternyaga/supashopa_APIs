<?php
define('DB_NAME', getenv('DB_NAME'));
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PASSWORD', getenv('DB_PASSWORD'));
define('DB_USER', getenv('DB_USER'));

/*DARAJA ENDPOINTS*/

define('AUTH_TOKEN_URL', ' https://api.safaricom.co.ke/oauth/v1/generate');
define('STK_PUSH',' https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
//define();
?>
