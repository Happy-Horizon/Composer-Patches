<?php

namespace HH\Patches\Data\Enums;

enum Type: string
{
    case File = 'file';
    case Directory = 'directory';
    case Invalid = 'invalid';
}