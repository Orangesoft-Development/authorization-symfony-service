<?php

namespace App\Tests\Functional\src\Service;

use App\Service\FileManager\FileManagerInterface;
use App\Storage\StreamedFile;

class AwsS3FileManager implements FileManagerInterface
{
    /**
     * @param StreamedFile $file
     *
     * @return bool
     */
    public function uploadStream(StreamedFile $file): bool
    {
        return true;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function remove(string $path): bool
    {
        return true;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool
    {
        return true;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getUrl(string $path): string
    {
        return 'url';
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getPresignedUrl(string $path): string
    {
        return 'presigned_url';
    }
}
