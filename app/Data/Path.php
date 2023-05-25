<?php

namespace HH\Patches\Data;

use HH\Patches\Data\Enums\Type;
class Path
{
    /**
     * @param string $fullPath
     * @param Type $type
     * @param bool $exists
     */
    public function __construct(
        protected string $fullPath,
        protected Type $type,
        protected bool $exists
    ) {}

    public function getFullPath(): string
    {
        return $this->fullPath;
    }
    
    public function getRelativePath(): string
    {
        return str_replace(BP . '/data', '', $this->fullPath);
    }
    
    public function getParent(): string
    {
        return str_replace(BP . '/data', '',dirname($this->fullPath, 1));
    }
    
    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function getExists(): bool
    {
        return $this->exists;
    }
}