<?php

declare(strict_types=1);

namespace Netzarbeiter\FlysystemHttp\Tests;

use GuzzleHttp\Psr7\Response;
use Netzarbeiter\FlysystemHttp\UrlAttributes;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class UrlAttributesTest extends TestCase
{
    public static function fromResponseDataProvider(): array
    {
        $variants = [];
        foreach (self::fromHeadersDataProvider() as $name => $data) {
            $variants[$name] = [
                'response' => new Response(
                    status: 200,
                    headers: $data['headers'],
                ),
                'path' => $data['path'],
                'expectedHeaders' => $data['expectedHeaders'],
                'expectedPath' => $data['expectedPath'],
                'expectedMimeType' => $data['expectedMimeType'],
                'expectedCharset' => $data['expectedCharset'],
                'expectedFileSize' => $data['expectedFileSize'],
                'expectedLastModified' => $data['expectedLastModified'],
            ];
        }

        return $variants;
    }

    public static function fromHeadersDataProvider(): array
    {
        return [
            'basic headers' => [
                'headers' => [
                    'Content-Type' => 'text/html; charset=ASCII',
                    'Last-Modified' => 'Wed, 21 Oct 2015 07:28:00 GMT',
                    'Content-Length' => '1234',
                    'Content-Disposition' => 'attachment; filename="example.txt"',
                ],
                'expectedHeaders' => [
                    'content-type' => 'text/html; charset=ASCII',
                    'last-modified' => 'Wed, 21 Oct 2015 07:28:00 GMT',
                    'content-length' => '1234',
                    'content-disposition' => 'attachment; filename="example.txt"',
                ],
                'path' => '/foo/bar',
                'expectedPath' => 'foo/bar',
                'expectedMimeType' => 'text/html',
                'expectedCharset' => 'ASCII',
                'expectedFileSize' => 1234,
                'expectedLastModified' => strtotime('Wed, 21 Oct 2015 07:28:00 GMT'),
            ],
            'missing values' => [
                'headers' => [
                    'Content-Type' => null,
                ],
                'expectedHeaders' => [],
                'path' => '/foo/bar',
                'expectedPath' => 'foo/bar',
                'expectedMimeType' => null,
                'expectedCharset' => 'UTF-8',
                'expectedFileSize' => null,
                'expectedLastModified' => null,
            ],
        ];
    }

    #[DataProvider('fromHeadersDataProvider')]
    public function testFromHeaders(
        array   $headers,
        array   $expectedHeaders,
        string  $path,
        ?string $expectedPath,
        ?string $expectedMimeType,
        ?string $expectedCharset,
        ?int    $expectedFileSize,
        ?int    $expectedLastModified,
    ): void
    {
        $attr = UrlAttributes::fromHeaders(headers: $headers, path: $path);
        $this->assertSame(expected: $expectedPath, actual: $attr->path());
        $this->assertSame(expected: $expectedMimeType, actual: $attr->mimeType());
        $this->assertSame(expected: $expectedCharset, actual: $attr->charset());
        $this->assertSame(expected: $expectedFileSize, actual: $attr->fileSize());
        $this->assertSame(expected: $expectedLastModified, actual: $attr->lastModified());
        $this->assertSame(expected: $expectedHeaders, actual: $attr->headers());
    }

    #[DataProvider('fromResponseDataProvider')]
    public function testFromResponse(
        ResponseInterface $response,
        string            $path,
        array             $expectedHeaders,
        ?string           $expectedPath,
        ?string           $expectedMimeType,
        ?string           $expectedCharset,
        ?int              $expectedFileSize,
        ?int              $expectedLastModified,
    ): void
    {
        $attr = UrlAttributes::fromResponse(response: $response, path: $path);
        $this->assertSame(expected: $expectedPath, actual: $attr->path());
        $this->assertSame(expected: $expectedMimeType, actual: $attr->mimeType());
        $this->assertSame(expected: $expectedCharset, actual: $attr->charset());
        $this->assertSame(expected: $expectedFileSize, actual: $attr->fileSize());
        $this->assertSame(expected: $expectedLastModified, actual: $attr->lastModified());
        $this->assertSame(expected: $expectedHeaders, actual: $attr->headers());
    }
}
