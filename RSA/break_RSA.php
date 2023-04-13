<?php
require_once 'math.php';
require_once 'RSA.php';
require_once '../config.php';


/**
 * @param $publicKey
 * @param $n
 * @return false|GMP|resource
 */
function breakRSA($publicKey, $n){
  $factors = primeFactors($n);
  $phi = gmp_mul($factors[0] - 1, $factors[1] - 1);
  return MathHelpers::getMultInverse($publicKey, $phi);  // private key
}

/**
 * @param $n
 * @return array
 */
function primeFactors($n):array
{
  // Print the number of
  // 2s that divide n
  $factors = [];
  while(gmp_mod($n , 2) == 0)
  {
    $factors[] = 2;
    $n = gmp_div($n,2);
  }

  // n must be odd at this
  // point. So we can skip
  // one element (Note i = i +2)
  for ($i = 3; $i <= gmp_sqrt($n);$i = $i + 2)
  {

    // While i divides n,
    // print i and divide n
    while (gmp_mod($n ,$i) == 0)
    {
      $factors[] = $i;
      $n = gmp_div($n , $i);
    }
  }

  // This condition is to
  // handle the case when n
  // is a prime number greater
  // than 2
  if ($n > 2)
    $factors[] = $n;

  return $factors;
}

/**
 * @return void
 * This function tries to break RSA for all key lengths, stores the results in a file
 */
function calculateTimeForAllKeys(){
//  $keyLength = 16;
  $filePtr = fopen('key_time.txt','w');
  fwrite($filePtr,"Key Length\tTime.\n");
  for ($keyLength = 16; $keyLength < 128; $keyLength++){
    $RSA = new RSA($keyLength);
    $startTime = microtime(true);
    $privateKey = breakRSA($RSA->getPublicKey(), $RSA->getN());
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;

    fwrite($filePtr,$keyLength . "\t" . $executionTime . "\n");
  }
  fclose($filePtr);
  echo "Finished\n";
}

$RSA = new RSA(KEY_LENGTH);
$publicKey = $RSA->getPublicKey();
$n = $RSA->getN();
$PQ = $RSA->getPQ();
$phi = gmp_mul($PQ[0], $PQ[1]);

printf("============================ Generated Keys =====================================================\n");
printf("Public Key : %s\n", $publicKey);
printf("P : %s\n", $PQ[0]);
printf("Q : %s\n", $PQ[1]);
printf("N : %s\n", $n);
printf("phi : %s\n", $phi);

printf("============================ Trying to Attack ====================================================\n");
printf("Enter the public Key \n");
$PUK = trim(fgets(STDIN));
printf("Enter the n value \n");
$n = trim(fgets(STDIN));

// Start Attacking
$startTime = microtime(true);
$privateKey = breakRSA($PUK, $n);
$endTime = microtime(true);
$executionTime = $endTime - $startTime;
printf("Your Private Key is : %s\n", $privateKey);
printf("Broken in : %s Micro seconds\n", $executionTime);
calculateTimeForAllKeys();


//function breakRSA($publicKey, $n){
//  $p = $q = 0;
//  for($i = 2; $i < ($n / 2); $i++){
//    if (gmp_mod($n , $i) == 0){
//      $p = $i;
//      $q = gmp_div($n, $p);
//      break;
//    }
//  }
//  echo $p, ', ', $q;die;
//  $phi = ($p - 1) * ($q - 1);
//  $privateKey = MathHelpers::getMultInverse($publicKey, $phi);
//  return $privateKey;
//}