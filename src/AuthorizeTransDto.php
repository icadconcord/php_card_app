<?php

namespace icadpay\checkout;

class AuthorizeTransDto 
{
    public $paymentId;
    public $amount;
    public $currency;
    public $otp;
}
