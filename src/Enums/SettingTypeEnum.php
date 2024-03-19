<?php

namespace Mantax559\LaravelSettings\Enums;

enum SettingTypeEnum: string
{
    case Array = 'array';
    case String = 'string';
    case Float = 'float';
    case Integer = 'integer';
    case Boolean = 'boolean';
}
