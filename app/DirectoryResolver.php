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
        if ($path === 'readme') {
            $fullPath = BP . DS . 'README.md';
            $exists = true;
            $type = Type::File;
        }
        
        return new Path(
            $fullPath,
            $type,
            $exists
        );
    }

    /**
     * @param Path $path
     * @return string[]
     */
    public function getDirectoryContents(Path $path): array
    {
        return array_filter(
            scandir($path->getFullPath()), 
            function ($content) {
                return preg_match(
                '/(^.*\.flag$)|(^\.{1,2})$/',
                    $content,
                ) == 0;
            }
        );
    }

    /**
     * @param Path $path
     * @return string|null
     */
    public function getVendor(Path $path): ?string
    {
        return array_reduce(
            scandir($path->getFullPath()),
            function ($result, $content) {
                preg_match('/^\.(.*)\.flag$/', $content, $matches);
                if (isset($matches[1])) {
                    $result = $matches[1];
                }
                return $result;
            },
            null
        );
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