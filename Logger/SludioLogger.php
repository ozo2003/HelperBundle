<?php

namespace Sludio\HelperBundle\Logger;

use Psr\Log\LoggerInterface;
use InvalidArgumentException;

class SludioLogger
{
    protected $logger;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct($logger = null)
    {
        if (!$logger instanceof LoggerInterface && null !== $logger) {
            throw new InvalidArgumentException(sprintf('SludioLogger needs PSR-3 LoggerInterface, "%s" was injected instead.', is_object($logger) ? get_class($logger) : gettype($logger)));
        }

        $this->logger = $logger;
    }

    /**
     * Logs a command
     *
     * @param string      $command Sludio command
     * @param null|string $error   Error message or null
     * @param string      $type    Log type
     */
    public function log($command, $error = null, $type = 'info')
    {
        $this->logger->{(string)$type}($command.': '.strtoupper((string)$type).($error !== null ? ': '.(string)$error : ''));
    }

    public function error($command, $error)
    {
        $this->log($command, $error, 'error');
    }
}