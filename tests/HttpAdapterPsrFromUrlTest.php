<?php

declare(strict_types=1);

namespace Netzarbeiter\FlysystemHttp\Tests;

/**
 * Test for the HTTP adapter using PSR-7 using a url
 */
class HttpAdapterPsrFromUrlTest extends \Netzarbeiter\FlysystemHttp\Tests\HttpAdapterTest
{
    /**
     * @inheritDoc
     */
    protected function createAdapter(): \League\Flysystem\FilesystemAdapter
    {
        return \Netzarbeiter\FlysystemHttp\HttpAdapterPsr::fromUrl($this->server->getServerRoot());
    }
}
