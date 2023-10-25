<?php

declare(strict_types=1);

namespace Netzarbeiter\FlysystemHttp;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemOperationFailed;

/**
 * Flysystem adapter for HTTP(S) urls
 */
abstract class HttpAdapter implements \League\Flysystem\FilesystemAdapter
{
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
        return $this->readMetadata($path);
    }

    /**
     * @inheritDoc
     */
    public function mimeType(string $path): FileAttributes
    {
        return $this->readMetadata($path);
    }

    /**
     * @inheritDoc
     */
    public function lastModified(string $path): FileAttributes
    {
        return $this->readMetadata($path);
    }

    /**
     * @inheritDoc
     */
    public function fileSize(string $path): FileAttributes
    {
        return $this->readMetadata($path);
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
    abstract protected function readMetadata(string $path): FileAttributes;

    /**
     * Parse mime type.
     *
     * @param string|null $value
     * @return string|null
     */
    protected function parseMimeType(?string $value): ?string
    {
        if (is_string($value)) {
            [$value,] = explode(';', $value);
        }

        // @todo Parse mime type from file extension

        return $value;
    }

    /**
     * Parse last modified.
     *
     * @param mixed $value
     * @return int|null
     */
    protected function parseLastModified(mixed $value): ?int
    {
        return match(true) {
            is_string($value) => strtotime($value),
            is_numeric($value) => (int)$value,
            default => null,
        };
    }

    /**
     * Parse file size.
     *
     * @param string|null $value
     * @return int|null
     */
    protected function parseFileSize(?string $value): ?int
    {
        if (is_numeric($value)) {
            return (int)$value;
        }

        return $value;
    }
}
