<?php

namespace Sludio\HelperBundle\Script\Utils;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class Logger
{
    protected $logger;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     *
     * @throws InvalidArgumentException
     */
    public function __construct($logger = null)
    {
        if (null !== $logger && !$logger instanceof LoggerInterface) {
            throw new InvalidArgumentException(sprintf('Logger needs PSR-3 LoggerInterface, "%s" was injected instead.', \is_object($logger) ? \get_class($logger) : \gettype($logger)));
        }

        $this->logger = $logger;
    }

    public function error($command, $error)
    {
        $this->log($command, $error, 'error');
    }

    /**
     * Logs a command
     *
     * @param string      $command Ccommand
     * @param null|string $error   Error message or null
     * @param string      $type    Log type
     */
    public function log($command, $error = null, $type = 'info')
    {
        $this->logger->{(string)$type}($command.': '.strtoupper((string)$type).($error !== null ? ': '.(string)$error : ''));
    }
}