<?php

namespace Sludio\HelperBundle\Logger;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use InvalidArgumentException;

class SludioLogger
{
    protected $logger;

    /**
     * Constructor.
     *
     * @param LoggerInterface|PsrLoggerInterface $logger A LoggerInterface instance
     */
    public function __construct($logger = null)
    {
        if (!$logger instanceof LoggerInterface && !$logger instanceof PsrLoggerInterface && null !== $logger) {
            throw new InvalidArgumentException(sprintf('SludioLogger needs either the HttpKernel LoggerInterface or PSR-3 LoggerInterface, "%s" was injected instead.',
                is_object($logger) ? get_class($logger) : gettype($logger)));
        }

        $this->logger = $logger;
    }

    /**
     * Logs a command
     *
     * @param string      $command    Sludio command
     * @param bool|string $error      Error message or null
     * @param string    $type         Log type
     */
    public function log($command, $error = false, $type = 'info')
    {
        $this->logger->{$type}($command.': '.strtoupper($type).($error ? ': '.$error : ''));
    }

    public function error($command, $error)
    {
        $this->log($command, $error, 'error');
    }
}