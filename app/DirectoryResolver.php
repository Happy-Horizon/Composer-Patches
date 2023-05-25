<?php

namespace HH\Patches;

use HH\Patches\Data\Enums\Type;
use HH\Patches\Data\Path;

class DirectoryResolver
{
    /**
     * @param string $path
     * @return Path
     */
    public function processPath(string $path): Path
    {
        $fullPath = rtrim(BP . '/data/patches/' . $path, '/');
        $exists = file_exists($fullPath);
        $type = Type::Invalid;
        if ($exists) {
            if (is_file($fullPath)) {
                $type = Type::File;
            } elseif (is_dir($fullPath)) {
                $type = Type::Directory;
            } 
        } 
        
        return new Path(
            $fullPath,
            $type,
            $exists
        );
    }
}