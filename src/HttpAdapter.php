<?php

declare(strict_types=1);

namespace Netzarbeiter\FlysystemHttp;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemOperationFailed;
use League\Flysystem\UnableToReadFile;
use Psr\Http\Message\StreamInterface;

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
            'follow_redirects' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function fileExists(string $path): bool
    {
        try {
            $request = new \GuzzleHttp\Psr7\Request('HEAD', $path);
            $response = $this->client->sendRequest($request);

            return str_starts_with((string)$response->getStatusCode(), '2');
        } catch (\Throwable $t) {
            return false;
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
        return $this->readFile($path)->getContents();
    }

    /**
     * @inheritDoc
     */
    public function readStream(string $path)
    {
        return $this->readFile($path)->detach();
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
     * Read file.
     *
     * @param string $path
     * @return StreamInterface
     */
    protected function readFile(string $path): StreamInterface
    {
        try {
            $request = new \GuzzleHttp\Psr7\Request('GET', $path);
            $response = $this->client->sendRequest($request);

            if (!str_starts_with((string)$response->getStatusCode(), '2')) {
                throw new UnableToReadFile('File not found');
            }

            return $response->getBody();
        } catch (\Throwable $t) {
            throw new UnableToReadFile($t->getMessage(), $t->getCode(), $t);
        }
    }

    /**
     * Get metadata.
     *
     * @param string $path
     * @return FileAttributes
     */
    protected function readMetadata(string $path): FileAttributes
    {
        try {
            $request = new \GuzzleHttp\Psr7\Request('HEAD', $path);
            $response = $this->client->sendRequest($request);

            if (!str_starts_with((string)$response->getStatusCode(), '2')) {
                throw new \League\Flysystem\UnableToRetrieveMetadata('File not found');
            }

            $headers = $response->getHeaders();

            $contentLength = $headers['Content-Length'][0] ?? null;
            if (is_numeric($contentLength)) {
                $contentLength = (int)$contentLength;
            }

            $mimeType = $headers['Content-type'][0] ?? null;
            if (is_string($mimeType)) {
                [$mimeType,] = explode(';', $headers['Content-type'][0] ?? '');
            }

            $lastModified = $headers['Last-Modified'][0] ?? null;
            $lastModified = match(true) {
                is_string($lastModified) => strtotime($lastModified),
                is_numeric($lastModified) => (int)$lastModified,
                default => null,
            };

            return new FileAttributes(
                $path,
                $contentLength,
                \League\Flysystem\Visibility::PUBLIC,
                $lastModified,
                $mimeType
            );
        } catch (\Throwable $t) {
            throw new \League\Flysystem\UnableToRetrieveMetadata($t->getMessage(), $t->getCode(), $t);
        }
    }

}
