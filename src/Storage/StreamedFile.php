<?php

namespace App\Storage;

use League\Flysystem\Util;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mime\MimeTypes;

class StreamedFile extends File
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var resource
     */
    private $resource;

    /**
     * @var string
     */
    private $mimeType;

    /**
     * @var int
     */
    private $size;

    /**
     * StreamedFile constructor.
     *
     * @param string $path
     * @param resource $resource
     */
    public function __construct(string $path, $resource)
    {
        $this->path = $path;
        $this->resource = $resource;

        $this->normalizePath();

        parent::__construct($this->path, false);
    }

    /**
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        if (null !== $this->mimeType) {
            return $this->mimeType;
        }

        return $this->mimeType = mime_content_type($this->resource);
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        if (null !== $this->size) {
            return $this->size;
        }

        return $this->size = Util::getStreamSize($this->resource);
    }

    /**
     * @return string|null
     */
    public function getExtension(): ?string
    {
        $mimeType = $this->getMimeType();

        $extensions = MimeTypes::getDefault()->getExtensions($mimeType);

        if (empty($extensions)) {
            return null;
        }

        foreach ($extensions as $extension) {
            if ($this->isExistsExtensionInPath($extension)) {
                return $extension;
            }
        }

        return $extensions[0];
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    private function normalizePath(): void
    {
        $extension = $this->getExtension();

        if ($extension !== null && !$this->isExistsExtensionInPath($extension)) {
            $this->path .= '.' . $extension;
        }
    }

    /**
     * @param string $extension
     *
     * @return bool
     */
    private function isExistsExtensionInPath(string $extension): bool
    {
        $extensionWithPrefix = '.' . $extension;

        return substr_compare($this->path, $extensionWithPrefix, -strlen($extensionWithPrefix)) === 0;
    }
}
