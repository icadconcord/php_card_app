<?php

include_once 'CardDto.php';

class PaymentDto
{
    public $amount;
    public $currency;
    public $customerId;
    public $transactionRef;
    public CardDto $card;

    
}
