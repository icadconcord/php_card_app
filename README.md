# Icadpay php card package
####
####
## Instantiate Service class

### $config = new IcadpayConfig();
### $config->merchantId = "test_pub_key";
### $config->merchantKey = "test_priv_key";
### $config->publicModulus = " ";
### $config->publicExponent = " ";
####
## $initService = new CardService($config);
####
####
####
## Send payment request
### $cardItem = new CardDto();
### $cardItem->pan = "5060990580000217499";
### $cardItem->expiryDate = "5003";
### $cardItem->cvv = "111";
### $cardItem->pin = "1111";
####
### $paymentItem = new PaymentDto();
### $paymentItem->amount = 100;
### $paymentItem->currency = "NGN";
### $paymentItem->customerId = "jjj@us.cc";
### $paymentItem->transactionRef = "iiwiwwmw3994";
### $paymentItem->card = $cardItem;
####
## $payReq = $initService->ProcessCard($paymentItem);
### $payRes = json_decode($payReq);
### //echo $payReq;
####
####
## Authorize transaction
### $authTr = new AuthorizeTransDto();
### $authTr->amount = 100;
### $authTr->paymentId = $payReq->paymentId;
### $authTr->otp = "123456";
## $authReq $initService->AuthorizeTransaction($authTr);
####
####
####
## Resend OTP
### $otpReq = new RequestOtpDto();
### $otpReq->amount = 100;
### $otpReq->currency = "NGN";
### $otpReq->paymentId = "232333";
## $resendOtp = $initService->ResendOtp();
