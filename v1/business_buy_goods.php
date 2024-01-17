<?php
$password = "REMOVED_ROTATE_CREDENTIAL";
$tokenURL="https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
$credentials= base64_encode("CVckGrldn5SCKxkjxtUvXQQu6tgCgeBX". ":". "NGl6j1m6AlZq1kVn");
$bytePassword = unpack('c*', $password);
// Assuming $byteArray contains the byte array to be encrypted

// Load the M-Pesa public key certificate
$publicKeyFile ='C:\xampp\htdocs\supashopa_APIs\v1\SandboxCertificate.cer'; // Path to the M-Pesa public key certificate
$publicKey = openssl_pkey_get_public(file_get_contents($publicKeyFile));

// Encrypt the byte array
$encryptedData = '';
foreach (array_chunk($bytePassword, 245) as $chunk) {
    $chunkEncrypted = '';
    $result = openssl_public_encrypt(implode('', array_map('chr', $chunk)), $chunkEncrypted, $publicKey);
    if (!$result) {
        die('Encryption failed');
    }
    $encryptedData .= $chunkEncrypted;
}

// Now $encryptedData contains the encrypted byte array
$securityCredential = base64_encode($encryptedData);
$file = "C:\Users\peterngari/security_credential.txt";
file_put_contents($file, base64_encode($encryptedData), FILE_APPEND);

$ch=curl_init($tokenURL);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response=curl_exec($ch);
curl_close($ch);
echo($response);
$response=json_decode($response);
$token=$response->{'access_token'};

echo ("Token= ". $token. "\n");

$buyGoodsURL="https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest";
$curl_post_data=array(
	'Initiator'=>"testapi",
  	'SecurityCredential'=>"HjVBdD7lDWd4KKXDI7i6x8GoYMBZ1+o2G3gT9/b8wvBKx2Jkj0cvt6LGe5MTa5it+MEvtcM0k21l0GjzcHPZXs71XI49bzHF7SIYqDc2qW2Pjs5rPhBA5MPtKnKnvY1H6SNHG2bVcfJOQQksTic86iaRbvbHdGueOSUxY1lIxHBpw5cMMD2X9/xzE2vszsODMdglLKYlzBJN009E1kuhIbtD1v3eVan7kOCpD6jkVMp4wFbRmsRuVFjH29kCfiyCZ53w9bwjaGsOhLSNI9Wv/1BqJWsPfT/sWGY1tDo2uFJPofJLRRWp1bq1EJugS3ORZbxy9pW74fgjyKQdudFOsQ==",
  	'CommandID'=> "BusinessPayBill",
  	'SenderIdentifierType'=> "4",
  	'RecieverIdentifierType'=>"4",
  	'Amount'=>"1",
  	'PartyA'=>"600990",
  	'PartyB'=>"600000",
  	'AccountReference'=>"174379",
  	'Requester'=>"254716689703",
  	'Remarks'=>"OK",
  	'QueueTimeOutURL'=>"https://probable-weasel-newly.ngrok-free.app/b2bcallback.php",
  	'ResultURL'=>"https://probable-weasel-newly.ngrok-free.app/b2bcallback.php"
  );
$payload=json_encode($curl_post_data);
$ch= curl_init($buyGoodsURL);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$token, 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result=json_decode(curl_exec($ch), true);
$responseCode = $result['ResponseCode'];
if ($responseCode == 0){
    $conversationId = $result['ConversationID'];
    $sessionId = session_id();
    $sessionId = "6e86-45dd-91ac-fd5d4178ab521891714";
    $db = new DBOperations("db_shops");
    $result = $db->setPayoutId($conversationId, $sessionId);
}
?>