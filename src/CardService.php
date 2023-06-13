<?php
namespace icadpay\checkout;

include_once __DIR__ . '/Crypt/RSA.php';
include_once __DIR__ . '/Math/BigInteger.php';

use icadpay\checkout\Crypt\Crypt_RSA;
use icadpay\checkout\Math\Math_BigInteger;

use icadpay\checkout\PaymentReqDto;
use icadpay\checkout\TokenDto;



class CardService
{
    private $merchantId;
    private $merchantKey;
    private $authToken;
    private $publicModulus;
    private $publicExponent;
    private $curl;
    // private TokenDto $tokenRes;
    private $testUrl = "https://icad-staging.icadpay.com/";
    private $prodUrl = "https://gateway.icadpay.com/";
    // private $baseUrl = "https://icad-staging.icadpay.com/";
    private $baseUrl;
    private $reqUrl;

    
    public function __construct()
    {
        $this->curl = curl_init();

    }

    public function __construct(IcadpayConfig $config)
    {
        $this->curl = curl_init();
        $this->merchantId = $config->merchantId;
        $this->merchantKey = $config->merchantKey;
        $this->publicExponent = $config->publicExponent;
        $this->publicModulus = $config->publicModulus;

        $envCheck = explode("_", $config->merchantId, 2);
        $this->baseUrl = $envCheck[0] == "test" ? $this->testUrl : $this->prodUrl;

        
      // echo $this->authToken;
      // echo "<br>";

      
        // echo "initializing...";
        $this->authToken = $this->GetAuthToken($config->merchantId, $config->merchantKey);

    }

    //### $initService->Authenticate() // for re authentication every 10mins after authentica
    public function Authenticate()
    {
      // // $this->curl = curl_init();
      // $this->merchantId = $config->merchantId;
      // $this->merchantKey = $config->merchantKey;
      // $this->publicExponent = $config->publicExponent;
      // $this->publicModulus = $config->publicModulus;

      $envCheck = explode("_", $this->merchantId, 2);
      $this->baseUrl = $envCheck[0] == "test" ? $this->testUrl : $this->prodUrl;

      
    // echo $this->authToken;
    // echo "<br>";

    
      // echo "initializing...";
      $this->authToken = $this->GetAuthToken($this->merchantId, $this->merchantKey);
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

    private function GetAuthToken($merchantId, $merchantKey)
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

    private function getEncryptedCard(CardDto $card)
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

    private function RequestHandler($request)
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