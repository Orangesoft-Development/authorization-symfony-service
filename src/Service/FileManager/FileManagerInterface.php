<?php

namespace App\Service\FileManager;

use App\Storage\StreamedFile;

interface FileManagerInterface
{
    /**
     * @param StreamedFile $file
     *
     * @return bool
     */
    public function uploadStream(StreamedFile $file): bool;

    /**
     * @param string $path
     *
     * @return bool
     */
    public function remove(string $path): bool;

    /**
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getUrl(string $path): string;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getPresignedUrl(string $path): string;
}
