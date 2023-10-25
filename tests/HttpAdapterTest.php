<?php

declare(strict_types=1);

namespace Netzarbeiter\FlysystemHttp\Tests;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\Visibility;
use Netzarbeiter\FlysystemHttp\FileOnlyFilesystem;
use Netzarbeiter\FlysystemHttp\ReadOnlyFilesystem;

/**
 * Test for the HTTP adapter
 */
abstract class HttpAdapterTest extends \PHPUnit\Framework\TestCase
{
    protected const MIME_TYPE = 'text/plain';
    protected const CONTENT_LENGTH = 12;
    protected const LAST_MODIFIED = 'Wed, 21 Oct 2015 07:28:00 GMT';
    protected const CONTENT = 'Hello World!';

    /**
     * Mock of a web server
     *
     * @var \donatj\MockWebServer\MockWebServer $server
     */
    protected \donatj\MockWebServer\MockWebServer $server;

    /**
     * Adapter to test
     *
     * @var \League\Flysystem\FilesystemAdapter $adapter
     */
    protected \League\Flysystem\FilesystemAdapter $adapter;

    /**
     * Create the adapter to test.
     *
     * @return \League\Flysystem\FilesystemAdapter
     */
    abstract protected function createAdapter(): \League\Flysystem\FilesystemAdapter;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->server = new \donatj\MockWebServer\MockWebServer();
        $this->server->setDefaultResponse(new \donatj\MockWebServer\Responses\NotFoundResponse());
        $this->server->setResponseOfPath(
            '/file.txt',
            new \donatj\MockWebServer\Response(
                self::CONTENT,
                [
                    'Content-Type' => self::MIME_TYPE,
                    'Content-Length' => self::CONTENT_LENGTH,
                    'Last-Modified' => self::LAST_MODIFIED,
                ]
            )
        );
        $this->server->start();

        $this->adapter = $this->createAdapter();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->server->stop();
    }

    /**
     * Test FilesystemAdapter::fileExists()
     */
    public function testFileExists(): void
    {
        $this->assertTrue($this->adapter->fileExists('/file.txt'));
    }

    /**
     * Test FilesystemAdapter::fileExists() for missing file
     */
    public function testFileExistsMissing(): void
    {
        $this->assertFalse($this->adapter->fileExists('/missing.txt'));
    }

    /**
     * Test FilesystemAdapter::directoryExists()
     */
    public function testDirectoryExists(): void
    {
        $this->expectException(FileOnlyFilesystem::class);
        $this->adapter->directoryExists('/directory');
    }

    /**
     * Test FilesystemAdapter::write()
     */
    public function testWrite(): void
    {
        $this->expectException(ReadOnlyFilesystem::class);
        $this->adapter->write('/file.txt', 'Hallo Welt!', new Config());
    }

    /**
     * Test FilesystemAdapter::writeStream()
     */
    public function testWriteStream(): void
    {
        $this->expectException(ReadOnlyFilesystem::class);
        $this->adapter->write('/file.txt', 'Hallo Welt!', new Config());
    }

    /**
     * Test FilesystemAdapter::read()
     */
    public function testRead(): void
    {
        $this->assertEquals(self::CONTENT, $this->adapter->read('/file.txt'));
    }

    /**
     * Test FilesystemAdapter::read() for missing file
     */
    public function testReadMissing(): void
    {
        $this->expectException(UnableToReadFile::class);
        $this->assertEquals(self::CONTENT, $this->adapter->read('/missing.txt'));
    }

    /**
     * Test FilesystemAdapter::readStream()
     */
    public function testReadStream(): void
    {
        $stream = $this->adapter->readStream('/file.txt');
        $this->assertIsResource($stream);
        $this->assertEquals(self::CONTENT, stream_get_contents($stream));
        fclose($stream);
    }

    /**
     * Test FilesystemAdapter::readStream() for missing file
     */
    public function testReadStreamMissing(): void
    {
        $this->expectException(UnableToReadFile::class);
        $this->assertEquals(self::CONTENT, $this->adapter->readStream('/missing.txt'));
    }

    /**
     * Test FilesystemAdapter::delete()
     */
    public function testDelete(): void
    {
        $this->expectException(ReadOnlyFilesystem::class);
        $this->adapter->delete('/file.txt');
    }

    /**
     * Test FilesystemAdapter::deleteDirectory()
     */
    public function testDeleteDirectory(): void
    {
        $this->expectException(ReadOnlyFilesystem::class);
        $this->adapter->deleteDirectory('/directory');
    }

    /**
     * Test FilesystemAdapter::createDirectory()
     */
    public function testCreateDirectory(): void
    {
        $this->expectException(ReadOnlyFilesystem::class);
        $this->adapter->createDirectory('/directory',  new Config());
    }

    /**
     * Test FilesystemAdapter::setVisibility()
     */
    public function testSetVisibility(): void
    {
        $this->expectException(ReadOnlyFilesystem::class);
        $this->adapter->setVisibility('/file.txt', Visibility::PUBLIC);
    }

    /**
     * Test FilesystemAdapter::visibility()
     */
    public function testVisibility(): void
    {
        $fileAttributes = $this->adapter->visibility('/file.txt');
        $this->assertInstanceOf(FileAttributes::class, $fileAttributes);
        $this->assertEquals(Visibility::PUBLIC, $fileAttributes->visibility());
    }

    /**
     * Test FilesystemAdapter::visibility()
     */
    public function testVisibilityMissing(): void
    {
        $this->expectException(UnableToRetrieveMetadata::class);
        $this->adapter->visibility('/missing.txt');
    }

    /**
     * Test FilesystemAdapter::mimeType()
     */
    public function testMimeType(): void
    {
        $fileAttributes = $this->adapter->visibility('/file.txt');
        $this->assertInstanceOf(FileAttributes::class, $fileAttributes);
        $this->assertEquals(self::MIME_TYPE, $fileAttributes->mimeType());
    }

    /**
     * Test FilesystemAdapter::mimeType()
     */
    public function testMimeTypeMissing(): void
    {
        $this->expectException(UnableToRetrieveMetadata::class);
        $this->adapter->mimeType('/missing.txt');
    }

    /**
     * Test FilesystemAdapter::lastModified()
     */
    public function testLastModified(): void
    {
        $fileAttributes = $this->adapter->visibility('/file.txt');
        $this->assertInstanceOf(FileAttributes::class, $fileAttributes);
        $this->assertEquals(strtotime(self::LAST_MODIFIED), $fileAttributes->lastModified());
    }

    /**
     * Test FilesystemAdapter::lastModified()
     */
    public function testLastModifiedMissing(): void
    {
        $this->expectException(UnableToRetrieveMetadata::class);
        $this->adapter->lastModified('/missing.txt');
    }

    /**
     * Test FilesystemAdapter::fileExists()
     */
    public function testFileSize(): void
    {
        $fileAttributes = $this->adapter->visibility('/file.txt');
        $this->assertInstanceOf(FileAttributes::class, $fileAttributes);
        $this->assertEquals(self::CONTENT_LENGTH, $fileAttributes->fileSize());
    }

    /**
     * Test FilesystemAdapter::fileExists()
     */
    public function testFileSizeMissing(): void
    {
        $this->expectException(UnableToRetrieveMetadata::class);
        $this->adapter->fileSize('/missing.txt');
    }

    /**
     * Test FilesystemAdapter::listContents()
     */
    public function testListContents(): void
    {
        $this->expectException(FileOnlyFilesystem::class);
        $this->adapter->listContents('/directory', true);
    }

    /**
     * Test FilesystemAdapter::move()
     */
    public function testMove(): void
    {
        $this->expectException(ReadOnlyFilesystem::class);
        $this->adapter->move('/source.txt', '/destination.txt', new Config());
    }

    /**
     * Test FilesystemAdapter::copy()
     */
    public function testCopy(): void
    {
        $this->expectException(ReadOnlyFilesystem::class);
        $this->adapter->copy('/source.txt', '/destination.txt', new Config());
    }
}
