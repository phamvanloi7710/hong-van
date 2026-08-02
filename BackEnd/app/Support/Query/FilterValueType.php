<?php

namespace App\Support\Query;

enum FilterValueType: string
{
    case String = 'string';
    case Integer = 'integer';
    case Boolean = 'boolean';
}
