<?php

declare(strict_types=1);

namespace Netzarbeiter\FlysystemHttp;

use League\Flysystem\FileAttributes;
use League\Flysystem\UnableToReadFile;

/**
 * Flysystem adapter for HTTP(S) urls using PHP streams
 *
 * @see https://github.com/twistor/flysystem-http
 */
class HttpAdapterStream extends HttpAdapter
{
    /**
     * Base url
     *
     * @var string
     */
    protected string $url;

    /**
     * Context
     *
     * @var array
     */
    protected array $context;

    /**
     * HttpAdapterStream constructor.
     *
     * @param string $url
     * @param array $context
     */
    public function __construct(string $url, array $context = [])
    {
        // Check if we have a valid url.
        $url = filter_var($url, FILTER_VALIDATE_URL);
        if ($url === false) {
            throw new \InvalidArgumentException('Invalid base url');
        }

        // Remove trainling slash (will be added in buildUrl()).
        $this->url = rtrim($url, '/');

        // Add in some safe defaults for SSL/TLS.
        $this->context = $context + [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'SNI_enabled' => true,
                'disable_compression' => true,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function fileExists(string $path): bool
    {
        return $this->head($path) !== null;
    }

    /**
     * @inheritDoc
     */
    public function read(string $path): string
    {
        $context = stream_context_create($this->context);
        $content = @file_get_contents($this->buildUrl($path), false, $context);

        if ($content === false) {
            throw new UnableToReadFile('File not found');
        }

        return $content;
    }

    /**
     * @inheritDoc
     */
    public function readStream(string $path)
    {
        $context = stream_context_create($this->context);
        $stream = @fopen($this->buildUrl($path), 'rb', false, $context);

        if ($stream === false) {
            throw new UnableToReadFile('File not found');
        }

        return $stream;
    }

    /**
     * Build url.
     *
     * @param string $path
     * @return string
     */
    protected function buildUrl(string $path): string
    {
        return $this->url . '/' . ltrim($path, '/');
    }

    /**
     * Make HEAD request.
     *
     * @param string $path
     * @return array|null
     */
    protected function head(string $path): ?array
    {
        $defaults = stream_context_get_options(stream_context_get_default());

        $options = $this->context;
        $options['http']['method'] = 'HEAD';
        stream_context_set_default($options);

        $headers = get_headers($this->buildUrl($path), true);

        stream_context_set_default($defaults);

        if ($headers === false || !preg_match('~^HTTP/\d(\.\d)? 2~', $headers[0])) {
            return null;
        }

        return array_change_key_case($headers);
    }

    /**
     * Get metadata.
     *
     * @param string $path
     * @return FileAttributes
     */
    protected function readMetadata(string $path): FileAttributes
    {
        $headers = $this->head($path);

        if ($headers === null) {
            throw new \League\Flysystem\UnableToRetrieveMetadata();
        }

        return new FileAttributes(
            $path,
            $this->parseFileSize($headers['content-length'] ?? null),
            \League\Flysystem\Visibility::PUBLIC,
            $this->parseLastModified($headers['last-modified'] ?? null),
            $this->parseMimeType($headers['content-type'] ?? null)
        );
    }
}
