<?php

declare(strict_types=1);

namespace Netzarbeiter\FlysystemHttp;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemOperationFailed;

/**
 * Flysystem adapter for HTTP(S) urls
 */
class HttpAdapter implements \League\Flysystem\FilesystemAdapter
{
    /**
     * HttpAdapter constructor.
     *
     * @param string $base
     */
    public function __construct(protected string $base)
    {
    }

    /**
     * @inheritDoc
     */
    public function fileExists(string $path): bool
    {
        // TODO: Implement fileExists() method.
    }

    /**
     * @inheritDoc
     */
    public function directoryExists(string $path): bool
    {
        throw new FileOnlyFilesystem(FilesystemOperationFailed::OPERATION_DIRECTORY_EXISTS);
    }

    /**
     * @inheritDoc
     */
    public function write(string $path, string $contents, Config $config): void
    {
        throw new ReadOnlyFilesystem(FilesystemOperationFailed::OPERATION_WRITE);
    }

    /**
     * @inheritDoc
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        throw new ReadOnlyFilesystem(FilesystemOperationFailed::OPERATION_WRITE);
    }

    /**
     * @inheritDoc
     */
    public function read(string $path): string
    {
        // TODO: Implement read() method.
    }

    /**
     * @inheritDoc
     */
    public function readStream(string $path)
    {
        // TODO: Implement readStream() method.
    }

    /**
     * @inheritDoc
     */
    public function delete(string $path): void
    {
        throw new ReadOnlyFilesystem(FilesystemOperationFailed::OPERATION_DELETE);
    }

    /**
     * @inheritDoc
     */
    public function deleteDirectory(string $path): void
    {
        throw new ReadOnlyFilesystem(FilesystemOperationFailed::OPERATION_DELETE_DIRECTORY);
    }

    /**
     * @inheritDoc
     */
    public function createDirectory(string $path, Config $config): void
    {
        throw new ReadOnlyFilesystem(FilesystemOperationFailed::OPERATION_CREATE_DIRECTORY);
    }

    /**
     * @inheritDoc
     */
    public function setVisibility(string $path, string $visibility): void
    {
        throw new ReadOnlyFilesystem(FilesystemOperationFailed::OPERATION_SET_VISIBILITY);
    }

    /**
     * @inheritDoc
     */
    public function visibility(string $path): FileAttributes
    {
        // TODO: Implement visibility() method.
    }

    /**
     * @inheritDoc
     */
    public function mimeType(string $path): FileAttributes
    {
        // TODO: Implement mimeType() method.
    }

    /**
     * @inheritDoc
     */
    public function lastModified(string $path): FileAttributes
    {
        // TODO: Implement lastModified() method.
    }

    /**
     * @inheritDoc
     */
    public function fileSize(string $path): FileAttributes
    {
        // TODO: Implement fileSize() method.
    }

    /**
     * @inheritDoc
     */
    public function listContents(string $path, bool $deep): iterable
    {
        throw new FileOnlyFilesystem(FilesystemOperationFailed::OPERATION_LIST_CONTENTS);
    }

    /**
     * @inheritDoc
     */
    public function move(string $source, string $destination, Config $config): void
    {
        throw new ReadOnlyFilesystem(FilesystemOperationFailed::OPERATION_MOVE);
    }

    /**
     * @inheritDoc
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        throw new ReadOnlyFilesystem(FilesystemOperationFailed::OPERATION_COPY);
    }

    /**
     * Get metadata.
     *
     * @param string $path
     * @return FileAttributes
     */
    protected function getMetadata(string $path): FileAttributes
    {
        // TODO: Implement getMetadata() method.
    }

}
