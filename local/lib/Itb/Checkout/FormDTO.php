<?php
namespace Itb\Checkout;

class FormDTO
{
    public string $email = '';
    public string $phone = '';
    public string $fio = '';
    public string $legalInn = '';
    public string $legalName = '';
    public string $legalAddress = '';
    public bool $legalAddressCheck = false;
    public string $legalActualAddress = '';
}
