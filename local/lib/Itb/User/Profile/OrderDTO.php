<?php
namespace Itb\User\Profile;

class OrderDTO 
{
    public int $id = 0;
    public bool $isPaid = false;
    public bool $isCanceled = false;
    public bool $isSuccess = false;
    public string $status = '';
    public string $recipient = '';
    public string $delivery;
    public string $payment;
    public string $address;
    public string $date = '';
    public array $items = [];
    public array $summary = [];
    public string $paymentLink = '';
}