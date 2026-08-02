<?php

namespace App\Support\Query;

enum FilterOperator: string
{
    case Equals = 'equals';
    case Contains = 'contains';
}
