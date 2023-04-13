<?php
require_once "../RSA/RSA.php";
require_once '../config.php';

// Initiate the server socket
set_time_limit(0);
$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die('Could not create socket \n');
$result = socket_bind($socket, HOST, PORT) or die('Could not bind socket.\n');
$result = socket_listen($socket, 3)  or die('Could not setup the listener.\n');
printf("Listening for any connections... \n");

// Generating keys of RSA
$RSA = new RSA(KEY_LENGTH);
$PQ  = $RSA->getPQ();
$myPublicKey = $RSA->getPublicKey();

// sending the initial info to the other side
$infoAsString = implode(',', [$PQ[0], $PQ[1], $myPublicKey]);
printf("Prime1, Prime2, My Public Key => %s \n", $infoAsString);
$accept = socket_accept($socket) or die("Could not accept the incoming msg.\n");
socket_write($accept, $infoAsString, strlen($infoAsString)) or die("Couldn't write the public numbers to the other side.\n");

// Getting the public key of the other side
printf("Enter the public key of your friend \n");
$senderPublicKey = rtrim(fgets(STDIN));


while(true){
  // reading an incoming msg
  $accept = socket_accept($socket) or die('Could not accept the incoming msg.\n');
  $message = socket_read($accept, MSG_MAX_LENGTH) or die('Could read the incoming msg.\n');
  $messagePackets = explode(',', $message);
  $decryptedMessage = $RSA->decrypt($messagePackets);
  printf("Client: %s \n", $decryptedMessage);

  // Writing a msg to the socket
  printf("Enter Reply: ");
  $input = rtrim(fgets(STDIN));
  $encryptedPackets = $RSA->encrypt($input, $senderPublicKey);
  $encryptedPacketsAsString = implode(',', $encryptedPackets);
  socket_write($accept, $encryptedPacketsAsString, strlen($encryptedPacketsAsString)) or die('Could write the msg.\n');
}

