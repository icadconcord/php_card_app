<?php
namespace icadpay\checkout;

use icadpay\checkout\Crypt\Crypt_RSA;
use icadpay\checkout\Math\Math_BigInteger;

use icadpay\checkout\PaymentReqDto;
use icadpay\checkout\TokenDto;



class CardService
{
    private $merchantId;
    private $merchantKey;
    private $authToken;
    private $publicModulus = "a556031686505e1c534b7b9632a4ae892a8bd2452f02f70229527f3726364eb68eb6677571369177c2befdd4b40488d71a1d21a24dc41349aa71c0c7713f56b114a867bad3983850287997d1074f0fba7fbc22796bcbcf5bd63c7933edda1b2dd5ab52f98806c64dfaf3b2fd2154ec883b693c46fde5d091973b6a47f8179b11cd5042016378d582456451cfb864da66c812151e700103c62c5f9c0e8bf6d2aabdde2b80c360f6635b513d28d64dac947cd10aa80827fe4ac4dc78208389d3281176dee53c97c4723c3f4126e06ee5824915e22ff4e7ff572784ee57ae543bcd366fb16401eec8d8d184c2a4fe640db47b659f80348e23acd8575700334ed84d";
    private $publicExponent = "010001";
    private $curl;
    // private TokenDto $tokenRes;
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