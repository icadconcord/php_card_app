<?php

namespace icadpay\checkout;

use icadpay\checkout\CardDto;


class PaymentDto
{
    public $amount;
    public $currency;
    public $customerId;
    public $transactionRef;
    public CardDto $card;
}
