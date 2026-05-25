<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Enum;

enum BillingType: string
{
    case Boleto     = 'BOLETO';
    case Pix        = 'PIX';
    case CreditCard = 'CREDIT_CARD';
    case Undefined  = 'UNDEFINED';
}
