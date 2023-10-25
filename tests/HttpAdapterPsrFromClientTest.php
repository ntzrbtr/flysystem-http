<?php

declare(strict_types=1);

namespace Netzarbeiter\FlysystemHttp\Tests;

/**
 * Test for the HTTP adapter using PSR-7 using a predefined client
 */
class HttpAdapterPsrFromClientTest extends HttpAdapterTest
{
    /**
     * @inheritDoc
     */
    protected function createAdapter(): \League\Flysystem\FilesystemAdapter
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->server->getServerRoot(),
            'follow_redirects' => true,
        ]);

        return new \Netzarbeiter\FlysystemHttp\HttpAdapterPsr($client);
    }
}
