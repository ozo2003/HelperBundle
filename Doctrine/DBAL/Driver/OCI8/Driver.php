<?php

namespace Sludio\HelperBundle\Doctrine\DBAL\Driver\OCI8;

use Doctrine\DBAL\DBALException;
use Sludio\HelperBundle\Doctrine\DBAL\OCI8\OCI8Connection;

class Driver extends \Doctrine\DBAL\Driver\OCI8\Driver
{
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        try {
            return new OCI8Connection(
                $username,
                $password,
                $this->_constructDsn($params),
                isset($params['charset']) ? $params['charset'] : null,
                isset($params['sessionMode']) ? $params['sessionMode'] : OCI_DEFAULT,
                isset($params['persistent']) ? $params['persistent'] : false
            );
        } catch (OCI8Exception $e) {
            throw DBALException::driverException($this, $e);
        }
    }
}
