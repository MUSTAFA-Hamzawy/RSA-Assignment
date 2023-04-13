<?php
require_once "../RSA/RSA.php";
require_once '../config.php';

// getting the primes and the public key from the server.
$socket = socket_create(AF_INET, SOCK_STREAM, 0);
socket_connect($socket, HOST, PORT);
$message = socket_read($socket, MSG_MAX_LENGTH) or die('Could read the public numbers.\n');
printf("I got this info from the server [P, Q, Public Key]: %s \n", $message);
$info = explode(',', $message);
$P = $info[0];
$Q = $info[1];
$senderPublicKey = $info[2];

// Generating keys of RSA
$RSA = new RSA(KEY_LENGTH);
$RSA->generateKeysPair($P, $Q);
$myPublicKey = $RSA->getPublicKey();
printf("My Public Key %s \n", $myPublicKey);


while(true){
  // Writing a msg to the socket
  $socket = socket_create(AF_INET, SOCK_STREAM, 0);
  socket_connect($socket, HOST, PORT);
  $input = rtrim(fgets(STDIN));
  $encryptedPackets = $RSA->encrypt($input, $senderPublicKey);
  $encryptedPacketsAsString = implode(',', $encryptedPackets);
  socket_write($socket, $encryptedPacketsAsString, strlen($encryptedPacketsAsString));

  // reading an incoming msg
  $message = socket_read($socket, MSG_MAX_LENGTH) or die('Could read the incoming msg.\n');
//  echo "received : " . $message . "\n";
  $messagePackets = explode(',', $message);
  $decryptedMessage = $RSA->decrypt($messagePackets);
  printf("Server: %s \n", $decryptedMessage);
}