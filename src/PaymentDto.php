<?php

namespace icadpay\checkout;

class PaymentDto
{
    public $amount;
    public $currency;
    public $customerId;
    public $transactionRef;
    public CardDto $card;

    
}
