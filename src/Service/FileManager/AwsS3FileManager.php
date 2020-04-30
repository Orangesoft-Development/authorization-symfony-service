<?php

namespace App\Service\FileManager;

use App\Storage\StreamedFile;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AwsS3FileManager implements FileManagerInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var int
     */
    private $linkTtl;

    /**
     * FileUploader constructor.
     *
     * @param FilesystemInterface $filesystem
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(FilesystemInterface $filesystem, ParameterBagInterface $parameterBag) {
        $this->filesystem = $filesystem;
        $this->linkTtl = $parameterBag->get('aws_s3_link_ttl');
    }

    /**
     * @param StreamedFile $file
     *
     * @return bool
     */
    public function uploadStream(StreamedFile $file): bool
    {
        $params = [];

        if ($contentType = $file->getMimeType()) {
            $params['ContentType'] = $contentType;
        }

        if ($contentLength = $file->getSize()) {
            $params['ContentLength'] = $contentLength;
        }

        return $this->filesystem->putStream($file->getPath(), $file->getResource(), $params + [
            'ACL' => 'private', //public-read or private
        ]);
    }

    /**
     * @param string $path
     *
     * @return bool
     *
     * @throws FileNotFoundException
     */
    public function remove(string $path): bool
    {
        if (!$this->filesystem->has($path)) {
            throw new FileNotFoundException($path);
        }

        return $this->filesystem->getAdapter()->delete($path);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool
    {
        return $this->filesystem->has($path);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getUrl(string $path): string
    {
        /** @var AwsS3Adapter $adapter */
        $adapter = $this->filesystem->getAdapter();
        $bucket = $adapter->getBucket();
        $s3Client = $adapter->getClient();

        return $s3Client->getObjectUrl($bucket, $path);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getPresignedUrl(string $path): string
    {
        /** @var AwsS3Adapter $adapter */
        $adapter = $this->filesystem->getAdapter();
        $bucket = $adapter->getBucket();
        $s3Client = $adapter->getClient();

        $command = $s3Client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $path,
        ]);

        $expires = sprintf('+%d seconds', $this->linkTtl);
        $request = $s3Client->createPresignedRequest($command, $expires);

        return $request->getUri();
    }
}
