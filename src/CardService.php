<?php

// include_once 'HTTP/Request2.php';
include_once 'Constants.php';
include_once 'PaymentDto.php';
include_once 'PaymentReqDto.php';
include_once 'Token.php';
include_once 'AuthorizeTransDto.php';
include_once 'RequestOtpDto.php';
// include_once 'Token.php';
include_once __DIR__ . '/Crypt/RSA.php';
include_once __DIR__ . '/Math/BigInteger.php';


class CardService
{
    private $merchantId;
    private $merchantKey;
    private $authToken;
    private $publicModulus = Constants::PUBLICKEY_MODULUS;
    private $publicExponent = Constants::PUBLICKEY_EXPONENT;
    private $curl;
    // private Token $tokenRes;
    private $testUrl = "https://icad-staging.icadpay.com/";
    private $prodUrl = "https://icad-staging.icadpay.com/";
    private $baseUrl = "https://icad-staging.icadpay.com/";
    private $reqUrl;

    public function __construct($merchantId, $merchantKey)
    {
        $this->curl = curl_init();
        $this->merchantId = $merchantId;
        $this->merchantKey = $merchantKey;

        // echo "initializing...";
        $this->authToken = $this->GetAuthToken($merchantId, $merchantKey);
        
      // echo $this->authToken;
      // echo "<br>";

    }

    public function ProcessCard(PaymentDto $pay)
    {

      $newPay = new PaymentReqDto();
      $newPay->transactionRef = $pay->transactionRef;
      $newPay->amount = $pay->amount;
      $newPay->currency = $pay->currency;
      $newPay->customerId = $pay->customerId;
      $newPay->dataBlock = $this->getEncryptedCard($pay->card);

      $this->reqUrl = $this->baseUrl . "cardpayment";

      return $this->RequestHandler(json_encode($newPay), );
    }


    public function AuthorizeTransaction(AuthorizeTransDto $authTrans)
    {
      $this->reqUrl = $this->baseUrl . "submitotp";
      return $this->RequestHandler(json_encode($authTrans));
    }


    public function ResendOtp(RequestOtpDto $requestOtpDto)
    {
      $this->reqUrl = $this->baseUrl . "resendotp";
      return $this->RequestHandler(json_encode($requestOtpDto));
    }
    


    // public function VerifyTransaction(AuthorizeTransDto $authTrans)
    // {
    //   $this->reqUrl = $this->baseUrl . "submitotp";
    //   return $this->RequestHandler(json_encode($authTrans));
    // }

    public function GetAuthToken($merchantId, $merchantKey)
    {
      curl_setopt_array($this->curl, array(
        CURLOPT_URL => $this->baseUrl.'getToken',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'MerchantId='.$merchantId.'&MerchantSecrete='.$merchantKey.'',
        CURLOPT_HTTPHEADER => array(
          'Content-Type: application/x-www-form-urlencoded',
          // 'Authorization: {{apiKey}}'
        ),
      ));
      
      $response = curl_exec($this->curl);
      
      curl_close($this->curl);
      // echo $response;
      // echo "<br>";
      $tokenRes = json_decode($response); 
      return $tokenRes->token;
    }

    public function getEncryptedCard(CardDto $card)
    {

        $cardData = json_encode($card);


        $rsa = new Crypt_RSA();
        $modulus = new Math_BigInteger($this->publicModulus, 16);
        $exponent = new Math_BigInteger($this->publicExponent, 16);
        $rsa->loadKey(array('n' => $modulus, 'e' => $exponent));
        $rsa->setPublicKey();
        $pub_key = $rsa->getPublicKey();


        openssl_public_encrypt($cardData, $encryptedData, $pub_key);
        $encData = base64_encode($encryptedData);

        return $encData;
    }

    

    public function RequestHandler($request)
    {
      // echo $request;
      // echo "<br>";
      // echo "requesting...";
      curl_setopt_array($this->curl, array(
        CURLOPT_URL => $this->reqUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $request,
        CURLOPT_HTTPHEADER => array(
          'Content-Type: application/json',
          'Authorization: Bearer '.$this->authToken
        ),
      ));

      // echo $this->baseUrl."<br>";
      
      $response = curl_exec($this->curl);
      
      curl_close($this->curl);
      // echo $response."<br>";
  
      return $response;
    }
}