<?php

namespace HappyHorizon\Patches\Data;

use HappyHorizon\Patches\Data\Enums\Type;
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

    /**
     * @return string
     */
    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    /**
     * @return string
     */
    public function getRelativePath(): string
    {
        return str_replace(BP . '/data', '', $this->fullPath);
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return str_replace(BP . '/data', '',dirname($this->fullPath, 1));
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return match ($this->type) {
            Type::All => 'all',
            Type::Directory => 'dir',
            Type::File => pathinfo($this->getFullPath(), PATHINFO_EXTENSION),
            Type::Invalid => 'err'
        };
        
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        $content = '';
        if ($this->type === Type::File) {
            $content = file_get_contents($this->getFullPath());
        }
        return $content;
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