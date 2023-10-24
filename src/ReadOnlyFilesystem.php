<?php

declare(strict_types=1);

namespace Netzarbeiter\FlysystemHttp;

/**
 * Exception thrown whenever we try to write to a read-only filesystem
 */
class ReadOnlyFilesystem extends \RuntimeException implements \League\Flysystem\FilesystemOperationFailed
{
    /**
     * ReadOnlyFilesystem constructor.
     *
     * @param string $operation
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        protected string $operation,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function operation(): string
    {
        return $this->operation;
    }
}
