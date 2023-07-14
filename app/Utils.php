<?php

namespace HappyHorizon\Patches;

class Utils
{
    public function __construct() {
        
    }

    /**
     * @param string $path
     * @return string
     */
    public function getUrl(string $path = ''): string
    {
        return 'https://' . $_SERVER['SERVER_NAME'] . $path;
    }

    /**
     * @param string $patch
     * @param int $fallback
     * @return int
     */
    public function getPatchNumber(string $patch, int $fallback = 0): int
    {
        preg_match('/^(?:\/?.*\/)?(?<patch_number>\d{4})_.*\.patch$/', $patch, $patchNumber);
        return isset($patchNumber['patch_number']) ? (int)$patchNumber['patch_number'] : $fallback;
    }

    /**
     * @param string $patch
     * @param string $fallback
     * @return string
     */
    public function getPatchNumberAsString(string $patch, string $fallback = '0000'): string
    {
        preg_match('/^(?:\/?.*\/)?(?<patch_number>\d{4})_?.*\.patch$/', $patch, $patchNumber);
        return $patchNumber['patch_number'] ?? $fallback;
    }
}