<?php

namespace Mantax559\LaravelSettings\Enums;

use Mantax559\LaravelHelpers\Traits\EnumTrait;

enum SettingTypeEnum: string
{
    use EnumTrait;

    case Array = 'array';
    case String = 'string';
    case Float = 'float';
    case Integer = 'integer';
    case Boolean = 'boolean';
}
