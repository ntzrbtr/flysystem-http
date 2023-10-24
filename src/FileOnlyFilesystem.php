<?php

declare(strict_types=1);

namespace Netzarbeiter\FlysystemHttp;

/**
 * Exception thrown whenever we try to perform an operation on directories
 */
class FileOnlyFilesystem extends \RuntimeException implements \League\Flysystem\FilesystemOperationFailed
{
    /**
     * FileOnlyFilesystem constructor.
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
