<?php

declare(strict_types=1);

namespace Netzarbeiter\FlysystemHttp;

use League\Flysystem\FileAttributes;
use League\Flysystem\UnableToReadFile;
use Psr\Http\Message\StreamInterface;

/**
 * Flysystem adapter for HTTP(S) urls using PSR-7
 */
class HttpAdapterPsr extends HttpAdapter
{
    /**
     * HttpAdapterPsr constructor.
     *
     * @param \Psr\Http\Client\ClientInterface $client
     */
    public function __construct(protected \Psr\Http\Client\ClientInterface $client)
    {
    }

    /**
     * Create adapter from url.
     *
     * @param string $url
     * @param array $options
     * @return self
     */
    public static function fromUrl(string $url, array $options = []): self
    {
        // Check if we have a valid url.
        $url = filter_var($url, FILTER_VALIDATE_URL);
        if ($url === false) {
            throw new \InvalidArgumentException('Invalid base url');
        }

        // Make sure we have a trailing slash (see https://docs.guzzlephp.org/en/stable/quickstart.html#creating-a-client).
        if (parse_url($url, PHP_URL_PATH) !== null) {
            $url = rtrim($url, '/') . '/';
        }

        // Compose options.
        $options += [
            'base_uri' => $url,
            'follow_redirects' => true,
        ];

        return new static(new \GuzzleHttp\Client($options));
    }

    /**
     * @inheritDoc
     */
    public function fileExists(string $path): bool
    {
        try {
            $this->head($path);

            return true;
        } catch (\Throwable $t) {
            return false;
        }
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

            if (!\str_starts_with((string)$response->getStatusCode(), '2')) {
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
            $response = $this->head($path);
            $headers = array_change_key_case($response->getHeaders());

            return new FileAttributes(
                $path,
                $this->parseFileSize($headers['content-length'][0] ?? null),
                \League\Flysystem\Visibility::PUBLIC,
                $this->parseLastModified($headers['last-modified'][0] ?? null),
                $this->parseMimeType($headers['content-type'][0] ?? null)
            );
        } catch (\Throwable $t) {
            throw new \League\Flysystem\UnableToRetrieveMetadata($t->getMessage(), $t->getCode(), $t);
        }
    }

    /**
     * Make HEAD request.
     *
     * @param string $path
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function head(string $path): \Psr\Http\Message\ResponseInterface
    {
        $request = new \GuzzleHttp\Psr7\Request('HEAD', $path);
        $response = $this->client->sendRequest($request);

        if (!\str_starts_with((string)$response->getStatusCode(), '2')) {
            throw new \League\Flysystem\UnableToRetrieveMetadata('File not found');
        }

        return $response;
    }
}
