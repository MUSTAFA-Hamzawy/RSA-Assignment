<?php
require_once 'RSA/RSA.php';
require_once 'config.php';

if (isset($_POST['submit'])){
  $RSA = new RSA(KEY_LENGTH);
  $publicKey = $RSA->getPublicKey();

  $message = $_REQUEST['msg'];
  $encryptedPackets = $RSA->encrypt($message);
  $cipherText = $RSA->getCipherText($encryptedPackets);
  $encryptedPacketsAsString = implode(' ', $encryptedPackets);  // just for printing
  $plainText = $RSA->decrypt($encryptedPackets);


}
?>

<title>RSA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Comfortaa&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style/style.css" />
<!---------- CONTAINER CONTAINER ----------------->
<div class="content-container">
  <h2>RSA Encryption/Decryption</h2>

  <!-----FORM ------->
  <div class="row">


    <div class="form-container">
      <form action="" method="POST">
        <div class="row">
          <div class="col-20">
            <label for="msg">Message</label>
          </div>
          <div class="col-80">
            <textarea id="msg" name="msg" placeholder="Write something.." style="height:200px"></textarea>
          </div>
        </div>
        <div>
          <p>
            <?php
            if (isset($cipherText)) {
              echo 'Public Key : ' . $publicKey . '<br/> <br/>';
            }
            ?>
          <p>
            <?php
            if (isset($cipherText)) {
              echo 'Cipher Text : ' . $cipherText . '<br/> <br/>';
              echo 'Encrypted Packets : <br/>' . $encryptedPacketsAsString;
            }
            ?>
          </p>
          <p>
            <?php
            if (isset($plainText))
              echo 'Original Text After decrypting: ' . $plainText
            ?>
          </p>
        </div>
        <div class="row">
          <input type="submit" name="submit" value="Encrypt/Decrypt">
        </div>
      </form>
    </div>
  </div>

  <!------------- END FORM ----------------->

  <!------------- END CONTENT-CONTAINER ----------------------->
</div>
<!----------------------------------------------------------->
