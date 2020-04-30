<?php

namespace App\Tests\Service\FileManager;

use App\Service\FileManager\AwsS3FileManager;
use App\Storage\StreamedFile;
use Aws\CommandInterface;
use Aws\S3\S3ClientInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\RequestInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class AwsS3FileManagerTest extends TestCase
{
    /**
     * @var int
     */
    private $linkTtl;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var AwsS3FileManager
     */
    private $fileManager;

    protected function setUp()
    {
        $this->linkTtl = 3600;
        $this->filesystem = $this->createMock(Filesystem::class);
        $parameterBag = new ParameterBag([
            'aws_s3_link_ttl' => $this->linkTtl,
        ]);

        $this->fileManager = new AwsS3FileManager($this->filesystem, $parameterBag);
    }

    public function testUploadStream(): void
    {
        $streamFile = $this->getStreamFile();

        $this->filesystem
            ->expects($this->once())
            ->method('putStream')
            ->with(
                $streamFile->getPath(),
                $streamFile->getResource(),
                [
                    'ContentType' => $streamFile->getMimeType(),
                    'ContentLength' => $streamFile->getSize(),
                    'ACL' => 'private',
                ]
            )
            ->willReturn(true)
        ;

        $this->assertTrue($this->fileManager->uploadStream($streamFile));
    }

    /**
     * @throws FileNotFoundException
     */
    public function testRemoveExistsFile(): void
    {
        $filePath = 'file_path';

        $this->filesystem
            ->expects($this->once())
            ->method('has')
            ->with($filePath)
            ->willReturn(true)
        ;

        $adapter = $this->getAdapter();
        $adapter
            ->expects($this->once())
            ->method('delete')
            ->with($filePath)
            ->willReturn(true)
        ;

        $this->assertTrue($this->fileManager->remove($filePath));
    }

    public function testRemoveNotExistsFile(): void
    {
        $filePath = 'file_path';

        $this->filesystem
            ->expects($this->once())
            ->method('has')
            ->with($filePath)
            ->willReturn(false)
        ;

        $this->expectException(FileNotFoundException::class);

        $this->fileManager->remove($filePath);
    }

    public function testExists(): void
    {
        $filePath = 'file_path';

        $this->filesystem
            ->expects($this->once())
            ->method('has')
            ->with($filePath)
            ->willReturn(true)
        ;

        $this->assertTrue($this->fileManager->exists($filePath));
    }

    public function testGetUrl(): void
    {
        $filePath = 'file_path';
        $fileUrl = 'file_url';

        $adapter = $this->getAdapter();
        $bucket = $this->getBucket($adapter);
        $s3Client = $this->getS3Client($adapter);
        $s3Client
            ->expects($this->once())
            ->method('getObjectUrl')
            ->with($bucket, $filePath)
            ->willReturn($fileUrl)
        ;

        $this->assertSame($fileUrl, $this->fileManager->getUrl($filePath));
    }

    public function testGetPresignedUrl(): void
    {
        $filePath = 'file_path';
        $presignedUrl = 'presigned_url';

        $adapter = $this->getAdapter();
        $bucket = $this->getBucket($adapter);
        $s3Client = $this->getS3Client($adapter);

        /** @var CommandInterface|MockObject $command */
        $command = $this->createMock(CommandInterface::class);

        $s3Client
            ->expects($this->once())
            ->method('getCommand')
            ->with('GetObject', [
                'Bucket' => $bucket,
                'Key'    => $filePath,
            ])
            ->willReturn($command)
        ;

        /** @var RequestInterface|MockObject $request */
        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($presignedUrl)
        ;

        $expires = sprintf('+%d seconds', $this->linkTtl);
        $s3Client
            ->expects($this->once())
            ->method('createPresignedRequest')
            ->with($command, $expires)
            ->willReturn($request)
        ;

        $this->assertSame($presignedUrl, $this->fileManager->getPresignedUrl($filePath));
    }

    /**
     * @return StreamedFile|MockObject
     */
    private function getStreamFile(): MockObject
    {
        $streamFile = $this->createMock(StreamedFile::class);

        $streamFile
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/jpeg')
        ;

        $streamFile
            ->expects($this->any())
            ->method('getSize')
            ->willReturn(1)
        ;

        $streamFile
            ->expects($this->any())
            ->method('getPath')
            ->willReturn('file_path')
        ;

        $fileResource = fopen('php://memory', 'r+');
        $streamFile
            ->expects($this->any())
            ->method('getResource')
            ->willReturn($fileResource)
        ;

        return $streamFile;
    }

    /**
     * @return AwsS3Adapter|MockObject
     */
    private function getAdapter(): MockObject
    {
        /** @var AwsS3Adapter|MockObject $adapter */
        $adapter = $this->createMock(AwsS3Adapter::class);

        $this->filesystem
            ->expects($this->any())
            ->method('getAdapter')
            ->willReturn($adapter)
        ;

        return $adapter;
    }

    /**
     * @param AwsS3Adapter|MockObject $adapter
     *
     * @return string
     */
    private function getBucket(MockObject $adapter): string
    {
        $bucket = 'bucket';

        $adapter
            ->expects($this->any())
            ->method('getBucket')
            ->willReturn($bucket)
        ;

        return $bucket;
    }

    /**
     * @param S3ClientInterface|MockObject $adapter
     *
     * @return MockObject
     */
    private function getS3Client(MockObject $adapter): MockObject
    {
        /** @var S3ClientInterface|MockObject */
        $s3Client = $this->createMock(S3ClientInterface::class);

        $adapter
            ->expects($this->any())
            ->method('getClient')
            ->willReturn($s3Client)
        ;

        return $s3Client;
    }
}
