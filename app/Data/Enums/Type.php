<?php

namespace HappyHorizon\Patches\Data\Enums;

enum Type: string
{
    case All = 'all';
    case Directory = 'directory';
    case File = 'file';
    case Invalid = 'invalid';
}