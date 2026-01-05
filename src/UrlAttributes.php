<?php

declare(strict_types=1);

namespace Netzarbeiter\FlysystemHttp;

use League\Flysystem\FileAttributes;
use Psr\Http\Message\ResponseInterface;

class UrlAttributes extends FileAttributes
{
    public static function fromHeaders(array $headers, string $path): self
    {
        $headers = array_filter(
            array_change_key_case($headers, CASE_LOWER)
        );
        $parsedHeaders = self::parseHeaders($headers);

        return new self(
            path: $path,
            fileSize: $parsedHeaders['content-length'],
            visibility: \League\Flysystem\Visibility::PUBLIC,
            lastModified: $parsedHeaders['last-modified'],
            mimeType: $parsedHeaders['content-type'],
            extraMetadata: [
                'charset' => $parsedHeaders['charset'] ?? 'UTF-8',
                'headers' => $headers,
            ]
        );
    }

    public static function fromResponse(ResponseInterface $response, string $path): self
    {
        $normalizedHeaders = [];

        foreach ($response->getHeaders() as $key => $values) {
            if (is_array($values)) {
                $newValue = implode(',', array_filter(array_map('trim', $values)));
            } else {
                $newValue = trim((string)$values);
            }

            if ($newValue === '') {
                continue;
            }

            $normalizedHeaders[$key] = $newValue;
        }

        return self::fromHeaders($normalizedHeaders, $path);
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array<string, mixed>
     */
    private static function parseHeaders(array $headers): array
    {
        $parsedHeaders = [
            'content-type' => null,
            'charset' => null,
            'last-modified' => null,
            'content-length' => null,
        ];

        foreach ($headers as $key => $value) {
            $keyLower = strtolower((string) $key);
            $value = is_array($value) ? reset($value) : $value;

            switch ($keyLower) {
                case 'content-type':
                    [$mimeType, $charset] = self::splitContentType($value);
                    $parsedHeaders['content-type'] = $mimeType;
                    $parsedHeaders['charset'] = $charset;
                    break;

                case 'last-modified':
                    $parsedHeaders[$keyLower] = self::parseLastModified($value);
                    break;

                case 'content-length':
                    $parsedHeaders[$keyLower] = (int) $value;
                    break;

                default:
                    $parsedHeaders[$keyLower] = $value;
                    break;
            }
        }

        return $parsedHeaders;
    }

    public function charset(): string
    {
        return $this->meta('charset');
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->meta('headers');
    }

    private function meta(string $key, $default = null): mixed
    {
        $meta = $this->extraMetadata();
        return array_key_exists($key, $meta) ? $meta[$key] : $default;
    }

    private static function splitContentType(?string $contentType): array
    {
        if (!$contentType) {
            return [null, null];
        }

        $parts = explode(';', $contentType);
        $mediaType = trim($parts[0]);
        $charset = null;

        foreach ($parts as $part) {
            if (stripos($part, 'charset=') !== false) {
                $charset = trim(str_ireplace('charset=', '', $part));
                break;
            }
        }

        return [$mediaType, $charset];
    }

    /**
     * Parse last modified.
     *
     * @param mixed $value
     * @return int|null
     */
    private static function parseLastModified(mixed $value): ?int
    {
        return match(true) {
            is_string($value) => strtotime($value),
            is_numeric($value) => (int)$value,
            default => null,
        };
    }
}
