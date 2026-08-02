<?php

namespace App\Domain\Products;

enum ProductPriceMode: string
{
    case Fixed = 'fixed';
    case From = 'from';
    case Range = 'range';
    case Market = 'market';
    case Dealer = 'dealer';
    case Quantity = 'quantity';
    case Contact = 'contact';
}
