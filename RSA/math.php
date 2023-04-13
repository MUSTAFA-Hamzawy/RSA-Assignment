<?php

class MathHelpers{


  /**
   * @param $firstNumber
   * @param $secondNumber
   * @return bool
   */
  public static function checkNumbersCoprime($firstNumber, $secondNumber):bool
  {
    $gcd = gmp_gcd($firstNumber, $secondNumber);
    return $gcd == 1;
  }

  /**
   * @param $length
   * @return GMP
   */
  public static function generatePrimeNumber($length) {
    $min = gmp_pow(2, $length - 1);
    $max = gmp_pow(2, $length) - 1;
    while (true) {
      $num = gmp_random_range($min, $max);
      if (self::isPrime($num)) {
        return $num;
      }
    }
  }

  /**
   * @param $number
   * @return bool
   */
  public static function isPrime($number):bool
	{
		return gmp_prob_prime($number) > 0;  // this func returns 2 if the number is definitely a prime
	}

  /**
   * @param $number
   * @param $exponent
   * @param $modulus
   * @return GMP|resource
   * To raise number into power with modulo
   */
  public static function getModExp($number, $exponent, $modulus)
	{
		return gmp_powm($number, $exponent, $modulus);
	}

  /**
   * @param $number
   * @param $modulus
   * @return false|GMP|resource
   */
  public static function getMultInverse($number, $modulus)
	{
		return gmp_invert($number, $modulus);
	}

  /**
   * @param $number
   * @return int|mixed
   */
  public static function getCoprimeNumber($number) {
    $coprimeNumbers = [];
    for ($i = 2; $i <= $number; $i++) {
        if (gmp_gcd($i, $number) == 1)
            $coprimeNumbers[] = $i;
        if (sizeof($coprimeNumbers) >= 10)
          break;
    }
    shuffle($coprimeNumbers);
    return $coprimeNumbers[0];
  }
}
