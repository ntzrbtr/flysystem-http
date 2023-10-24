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
     * HTTP client
     *
     * @var \Psr\Http\Client\ClientInterface
     */
    protected \Psr\Http\Client\ClientInterface $client;

    /**
     * HttpAdapter constructor.
     *
     * @param string $base
     * @param \Psr\Http\Client\ClientInterface|null $client
     */
    public function __construct(protected string $base, \Psr\Http\Client\ClientInterface $client = null)
    {
        $url = filter_var($base, FILTER_VALIDATE_URL);
        if ($url === false) {
            throw new \InvalidArgumentException('Invalid base url');
        }

        $this->client = $client ?? new \GuzzleHttp\Client([
            'base_uri' => $url,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function fileExists(string $path): bool
    {
        try {
            $request = new \GuzzleHttp\Psr7\Request('HEAD', $path);
            $this->client->sendRequest($request);

            return true;
        } catch (\Psr\Http\Client\ClientExceptionInterface $e) {
            throw new \League\Flysystem\UnableToCheckFileExistence($e->getMessage(), $e->getCode(), $e);
        }
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
        return $this->getMetadata($path);
    }

    /**
     * @inheritDoc
     */
    public function mimeType(string $path): FileAttributes
    {
        return $this->getMetadata($path);
    }

    /**
     * @inheritDoc
     */
    public function lastModified(string $path): FileAttributes
    {
        return $this->getMetadata($path);
    }

    /**
     * @inheritDoc
     */
    public function fileSize(string $path): FileAttributes
    {
        return $this->getMetadata($path);
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
        try {
            $request = new \GuzzleHttp\Psr7\Request('HEAD', $path);
            $response = $this->client->sendRequest($request);
            $headers = $response->getHeaders();

            return new FileAttributes(
                $path,
                (int)$headers['Content-Length'][0],
                \League\Flysystem\Visibility::PUBLIC,
                (int)$headers['Last-Modified'][0],
                $headers['Content-Type'][0]
            );
        } catch (\Psr\Http\Client\ClientExceptionInterface $e) {
            throw new \League\Flysystem\UnableToRetrieveMetadata($e->getMessage(), $e->getCode(), $e);
        }
    }

}
