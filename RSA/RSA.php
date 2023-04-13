<?php
require_once 'math.php';

class RSA
{
	private $publicKey;
	private  $privateKey;
	private  $n;
	private  $phi;
	private $keyLength;
	private $alphabetsMapping;
  private $firstPrime, $secondPrime;

  /**
   * @param $keyLength
   */
  public function __construct($keyLength)
	{
    $this->keyLength = $keyLength;
		$this->alphabetsMapping = [];
		$char = 'a';
		for($i = 0; $i < 26; $i++){
			$this->alphabetsMapping[$char] = 10 + $i;
			++$char; 
		}
		
		$this->alphabetsMapping[' '] = 36;
		$this->alphabetsMapping['0'] = 0;
		$this->alphabetsMapping['1'] = 1;
		$this->alphabetsMapping['2'] = 2;
		$this->alphabetsMapping['3'] = 3;
		$this->alphabetsMapping['4'] = 4;
		$this->alphabetsMapping['5'] = 5;
		$this->alphabetsMapping['6'] = 6;
		$this->alphabetsMapping['7'] = 7;
		$this->alphabetsMapping['8'] = 8;
		$this->alphabetsMapping['9'] = 9;

    $this->generateKeys();
	}

  /**
   * To generate the keys of RSA
   */
  public function generateKeys()
	{
		// Generating two prime numbers ( p1 != p2 )
		$firstPrimeSize  = intdiv($this->keyLength, 2);
		$secondPrimeSize = $this->keyLength - $firstPrimeSize;
		do {
			$firstPrime  = MathHelpers::generatePrimeNumber($firstPrimeSize);  // p
			$secondPrime = MathHelpers::generatePrimeNumber($secondPrimeSize); // q
      if (!MathHelpers::isPrime($firstPrime) || !MathHelpers::isPrime($secondPrime)) {
        echo 'Generating the prime numbers.';
        $firstPrime = $secondPrime = 0; // Just to meet the condition of the loop
      }
		} while ($firstPrime === $secondPrime);

    $this->firstPrime = $firstPrime;
    $this->secondPrime = $secondPrime;
		// Generate keys
    $this->generateKeysPair($firstPrime, $secondPrime);
	}

  /**
   * @param $firstPrime
   * @param $secondPrime
   * @return void
   */
  public function generateKeysPair($firstPrime, $secondPrime)
	{
		// Calculating n ( modulus )
		$this->n = gmp_mul($firstPrime ,$secondPrime); // don't do this -> p*q , use gmp_mul for big integers

		// Calculating ϕ(n) = (p−1)(q− 1)
		$this->phi = gmp_mul($firstPrime - 1 ,$secondPrime - 1);

		// Checking that public key is coprime to ϕ($n)
    $this->publicKey  = MathHelpers::getCoprimeNumber($this->phi);
		if (!MathHelpers::checkNumbersCoprime($this->publicKey, $this->phi)) {
			echo('publicKey must be coprime to phi');die;
		}

		// Calculating the privateKey = $publicKey (mod ϕ($n)) yielding
		$this->privateKey = MathHelpers::getMultInverse($this->publicKey, $this->phi);
	}

  /**
   * @param string $message
   * @param $publicKey
   * @return array
   */
  public function encrypt(string $message, $publicKey = null): array
	{
    $encodedMsg = $this->encodeMessage(strtolower(trim($message)));
    $encryptedPackets = [];
    if ($publicKey){
      // chat mod
      foreach ($encodedMsg as $item)
        $encryptedPackets[] = MathHelpers::getModExp($item, $publicKey, $this->n);
    }else{
      // normal mod ( no need to pass the public key since there is only one person testing the algorithm )
      foreach ($encodedMsg as $item)
        $encryptedPackets[] = MathHelpers::getModExp($item, $this->publicKey, $this->n);
    }
    return $encryptedPackets;
	}

  /**
   * @param array $encryptedMsg
   * @return string
   */
  public function getCipherText(Array $encryptedMsg): string
  {
    // decoding the decryptedMsg to get the ciphertext
    $cipherText = '';
    foreach ($encryptedMsg as $item)
      $cipherText .= $this->decodeMessage($item);

    return $cipherText;
  }

  /**
   * @param array $encryptedPackets
   * @return string
   */
  public function decrypt(Array $encryptedPackets): string
	{
    $decrypted = [];
    foreach ($encryptedPackets as $item)
      $decrypted[] = MathHelpers::getModExp($item, $this->privateKey, $this->n);


    // Decoding to get the original text
    $plainText = '';
    foreach ($decrypted as $item)
      $plainText .= $this->decodeMessage($item);

    return $plainText;
	}

  /**
   * @param string $msg
   * @return array
   */
  public function encodeMessage(string $msg): array
	{
		str_replace("#","@", $msg);	// because we will make chunks separated by #

		$chunksAsString = chunk_split($msg, 5, '#');
		$chunksAsArray  = explode("#", $chunksAsString);
    array_pop($chunksAsArray);  // last element is always empty, we don't need it

		// padding the last chunk
		$lastChunk = end($chunksAsArray);
    $chunksAsArray[sizeof($chunksAsArray) - 1] .= str_repeat(' ', 5-strlen($lastChunk));
		$value = 0;
		$encodedMsg = [];
		foreach($chunksAsArray as $chunk){
			for($i = 0, $j = 4; $i < 5 && $j >= 0; $i++, $j--){
				$charValue = $this->alphabetsMapping[$chunk[$j]] ? $this->alphabetsMapping[$chunk[$j]] : $this->alphabetsMapping[' '];
				$value += pow(37, $i) * $charValue;
			}
			$encodedMsg[] = $value;
      $value = 0;
		}
		return $encodedMsg;
	}

  /**
   * @param $encodedMsg
   * @return string
   */
  public function decodeMessage($encodedMsg): string
	{
		$decodedMsg = '';
		for($i = 4; $i >= 0; $i--){
			$decodedValue = gmp_div_q($encodedMsg, pow(37, $i));
			$decodedMsg  .= array_search($decodedValue, $this->alphabetsMapping);
			$encodedMsg  %= pow(37, $i);
		}
		return $decodedMsg;
	}

  /**
   * @return mixed
   */
  public function getPublicKey(){
    return $this->publicKey;
  }

  /**
   * @return array
   */
  public function getPQ(): array{
    return [$this->firstPrime, $this->secondPrime];
  }

  /**
   * @return mixed
   */
  public function getN(){
    return $this->n;
  }

  // this function to test the attacking only

  /**
   * @param $privateKey
   * @return bool
   */
  public function compareWithPrivateKey($privateKey):bool{
    return gmp_cmp($privateKey, $this->privateKey) == 0;
  }
}
