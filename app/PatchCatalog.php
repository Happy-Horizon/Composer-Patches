<?php

namespace HappyHorizon\Patches;

use HappyHorizon\Patches\Data\Path;

class PatchCatalog
{
    private const UNKNOWN = 'unknown';

    /**
     * @var list<array{path: string, filename: string, package: string}>|null
     */
    private ?array $entries = null;

    public function __construct(
        protected DirectoryResolver $directoryResolver
    ) {
    }

    /**
     * @return list<array{path: string, filename: string, package: string}>
     */
    public function getAll(): array
    {
        if ($this->entries === null) {
            $this->entries = $this->build();
        }

        return $this->entries;
    }

    /**
     * @return array<string, int> package => patch count
     */
    public function getPackagesWithCounts(): array
    {
        $counts = [];
        foreach ($this->getAll() as $entry) {
            $package = $entry['package'];
            $counts[$package] = ($counts[$package] ?? 0) + 1;
        }
        ksort($counts, SORT_STRING | SORT_FLAG_CASE);

        return $counts;
    }

    /**
     * @return list<array{path: string, filename: string, package: string}>
     */
    public function filter(?string $module = null, ?string $query = null): array
    {
        $module = $module !== null ? trim($module) : null;
        $query = $query !== null ? trim($query) : null;

        return array_values(array_filter(
            $this->getAll(),
            function (array $entry) use ($module, $query): bool {
                if ($module !== null && $module !== '') {
                    if (strcasecmp($entry['package'], $module) !== 0) {
                        return false;
                    }
                }
                if ($query !== null && $query !== '') {
                    if (stripos($entry['package'], $query) === false) {
                        return false;
                    }
                }

                return true;
            }
        ));
    }

    /**
     * @return list<array{path: string, filename: string, package: string}>
     */
    protected function build(): array
    {
        $root = new Path(rtrim(BP . '/data/patches', DS), \HappyHorizon\Patches\Data\Enums\Type::All, true);
        $paths = $this->directoryResolver->getAllPatchFiles($root);
        $entries = [];

        foreach ($paths as $relativePath) {
            $normalized = '/' . ltrim(str_replace('\\', '/', $relativePath), '/');
            $entries[] = [
                'path' => $normalized,
                'filename' => basename($normalized),
                'package' => $this->resolvePackage($normalized),
            ];
        }

        usort($entries, function (array $a, array $b): int {
            $packageCmp = strcasecmp($a['package'], $b['package']);
            if ($packageCmp !== 0) {
                return $packageCmp;
            }

            return strcasecmp($a['filename'], $b['filename']);
        });

        return $entries;
    }

    protected function resolvePackage(string $relativePath): string
    {
        $parts = array_values(array_filter(explode('/', $relativePath), static fn ($p) => $p !== ''));
        // Expect: patches / … / filename.patch
        if (($parts[0] ?? '') === 'patches') {
            array_shift($parts);
        }

        $filename = array_pop($parts) ?? '';
        $parentDir = $parts !== [] ? $parts[array_key_last($parts)] : '';

        if ($this->isPackageDir($parentDir, $parts)) {
            return $this->composerPackage($parts, $parentDir);
        }

        $fromFilename = $this->packageFromFilename($filename, $parts);
        if ($fromFilename !== null) {
            return $fromFilename;
        }

        $fullPath = BP . DS . 'data' . DS . ltrim($relativePath, '/');
        $fromDiff = $this->packageFromDiff($fullPath);
        if ($fromDiff !== null) {
            return $fromDiff;
        }

        return self::UNKNOWN;
    }

    /**
     * @param string[] $pathParts Path segments after "patches", excluding filename
     */
    protected function isPackageDir(string $dir, array $pathParts): bool
    {
        if ($dir === '' || $dir === 'all') {
            return false;
        }
        if (preg_match('/^\d+\.\d+/', $dir) === 1) {
            return false;
        }
        if (preg_match('/^APSB/i', $dir) === 1) {
            return false;
        }

        if (($pathParts[0] ?? '') === 'third-party') {
            // third-party / vendor / package
            return count($pathParts) >= 3;
        }

        return str_starts_with($dir, 'module-')
            || in_array($dir, ['framework', 'magento2-base'], true);
    }

    /**
     * @param string[] $pathParts Path segments after "patches", excluding filename
     */
    protected function composerPackage(array $pathParts, string $packageName): string
    {
        if (($pathParts[0] ?? '') === 'third-party' && isset($pathParts[1])) {
            return $pathParts[1] . '/' . $packageName;
        }

        return 'magento/' . $packageName;
    }

    /**
     * @param string[] $pathParts Path segments after "patches", excluding filename
     */
    protected function packageFromFilename(string $filename, array $pathParts): ?string
    {
        if (preg_match('/_(?<pkg>module-[a-z0-9-]+|framework)\.patch$/i', $filename, $matches) !== 1) {
            return null;
        }

        $packageName = strtolower($matches['pkg']);
        if (($pathParts[0] ?? '') === 'third-party' && isset($pathParts[1])) {
            return $pathParts[1] . '/' . $packageName;
        }

        return 'magento/' . $packageName;
    }

    protected function packageFromDiff(string $fullPath): ?string
    {
        if (!is_file($fullPath)) {
            return null;
        }

        $handle = fopen($fullPath, 'rb');
        if ($handle === false) {
            return null;
        }

        try {
            $bytesRead = 0;
            while (!feof($handle) && $bytesRead < 8192) {
                $line = fgets($handle, 1024);
                if ($line === false) {
                    break;
                }
                $bytesRead += strlen($line);
                if (preg_match('#(?:^|\s)(?:a/|b/)?vendor/(?<vendor>[^/\s]+)/(?<package>[^/\s]+)/#', $line, $matches) === 1) {
                    return $matches['vendor'] . '/' . $matches['package'];
                }
            }
        } finally {
            fclose($handle);
        }

        return null;
    }
}
