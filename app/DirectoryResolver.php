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
        $fullPath = rtrim(BP . '/data/patches/' . $path, DS);
        $exists = file_exists($fullPath);
        $type = Type::Invalid;
        if ($exists) {
            if (is_file($fullPath)) {
                $type = Type::File;
            } elseif (is_dir($fullPath)) {
                $type = Type::Directory;
            } 
        }
        if ($path === 'all') {
            $fullPath = rtrim(BP . '/data/patches/', DS);
            $exists = true;
            $type = Type::All;
        }
        
        return new Path(
            $fullPath,
            $type,
            $exists
        );
    }

    /**
     * @param Path $path
     * @param bool $ignoreFlags
     * @return string[]
     */
    public function getDirectoryContents(Path $path, bool $ignoreFlags = false): array
    {
        $ignore = [
            '.',
            '..'
        ];
        if ($ignoreFlags) {
            $ignore[] = '.install.flag';
        }
        return array_diff(scandir($path->getFullPath()), $ignore);
    }

    /**
     * @return string[]
     */
    public function getAllPatchFiles(Path $path): array
    {
        $patches = [];
        $this->recursiveDirSearch($path->getFullPath(), '*.patch', $patches);;
        
        return $patches;
    }

    /**
     * @param string $path
     * @param string $pattern
     * @param array $results
     * @return void
     */
    protected function recursiveDirSearch(string $path, string $pattern, array &$results) {
        $files = glob($path . DS . $pattern);
        foreach ($files as $file) {
            $results[] = str_replace(BP . DS . 'data', '', $file);
        }
        $dirs = glob($path . DS . '*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $this->recursiveDirSearch($dir, $pattern, $results);
        }
    }
}