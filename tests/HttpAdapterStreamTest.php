<?php

declare(strict_types=1);

namespace Netzarbeiter\FlysystemHttp\Tests;

/**
 * Test for the HTTP adapter using PHP streams
 */
class HttpAdapterStreamTest extends HttpAdapterTest
{
    /**
     * @inheritDoc
     */
    protected function createAdapter(): \League\Flysystem\FilesystemAdapter
    {
        return new \Netzarbeiter\FlysystemHttp\HttpAdapterStream($this->server->getServerRoot());
    }
}
