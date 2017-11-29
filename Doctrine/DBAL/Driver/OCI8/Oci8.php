<?php

namespace Sludio\HelperBundle\Doctrine\DBAL\Driver\OCI8;

use PDO;

class Oci8 extends PDO
{
    // New param type for clob and blob support.
    const PARAM_BLOB = OCI_B_BLOB;
    const PARAM_CLOB = OCI_B_CLOB;
    const LOB_SQL = 0;
    const LOB_PL_SQL = 1;

    /**
     * @var resource Database handler
     */
    public $dbh;

    /**
     * @var array Driver options
     */
    private $options;

    /**
     * @var bool Whether currently in a transaction
     */
    private $inTransaction = false;

    /**
     * Creates a PDO instance representing a connection to a database
     *
     * @param        $dsn
     * @param        $username [optional]
     * @param        $password [optional]
     * @param null   $host
     * @param null   $dbName
     * @param null   $port
     * @param string $charset
     * @param array  $options  [optional]
     */
    public function __construct($dsn = null, $username = null, $password = null, $host = null, $dbName = null, $port = null, $charset = 'AL32UTF8', $options = [])
    {
        $this->connect($username, $password, $host, $dbName, $port, $charset, $options ?: []);
        $this->options = $options;
    }

    /**
     * Prepares a statement for execution and returns a statement object
     *
     * @param string $statement This must be a valid SQL statement for the
     *                          target database server.
     * @param array  $options   [optional] This array holds one or more key=>value
     *                          pairs to set attribute values for the PDOStatement object that this
     *                          method returns.
     *
     * @throws Oci8Exception
     * @return Oci8Statement
     */
    public function prepare($statement, array $options = [])
    {
        $sth = oci_parse($this->dbh, $statement);

        if (!$sth) {
            $e = oci_error($this->dbh);
            throw new Oci8Exception($e['message']);
        }

        if ($options === null) {
            $options = $this->options;
        }

        if (!is_array($options)) {
            $options = [];
        }

        return new Oci8Statement($sth, $this, $options);
    }

    /**
     * Initiates a transaction
     *
     * @throws Oci8Exception
     * @return bool TRUE on success or FALSE on failure
     */
    public function beginTransaction()
    {
        if ($this->inTransaction()) {
            throw new Oci8Exception('There is already an active transaction');
        }

        $this->inTransaction = true;

        return true;
    }

    /**
     * Checks if inside a transaction
     *
     * @return bool TRUE if a transaction is currently active, and FALSE if not.
     */
    public function inTransaction()
    {
        return $this->inTransaction;
    }

    /**
     * Commits a transaction
     *
     * @throws Oci8Exception
     * @return bool TRUE on success or FALSE on failure.
     */
    public function commit()
    {
        if (!$this->inTransaction()) {
            throw new Oci8Exception('There is no active transaction');
        }

        if (oci_commit($this->dbh)) {
            $this->inTransaction = false;

            return true;
        }

        return false;
    }

    /**
     * Rolls back a transaction
     *
     * @throws Oci8Exception
     * @return bool TRUE on success or FALSE on failure.
     */
    public function rollBack()
    {
        if (!$this->inTransaction()) {
            throw new Oci8Exception('There is no active transaction');
        }

        if (oci_rollback($this->dbh)) {
            $this->inTransaction = false;

            return true;
        }

        return false;
    }

    /**
     * Sets an attribute on the database handle
     *
     * @param int   $attribute
     * @param mixed $value
     *
     * @return bool TRUE on success or FALSE on failure.
     */
    public function setAttribute($attribute, $value)
    {
        $this->options[$attribute] = $value;

        return true;
    }

    /**
     * Executes an SQL statement and returns the number of affected rows
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @return int The number of rows that were modified or deleted by the SQL
     *   statement you issued.
     * @throws \Sludio\HelperBundle\Doctrine\DBAL\Driver\OCI8\Oci8Exception
     */
    public function exec($statement)
    {
        $stmt = $this->query($statement);

        return $stmt->rowCount();
    }

    /**
     * Executes an SQL statement, returning the results as a
     * Oci8Statement object
     *
     * @param string     $statement The SQL statement to prepare and execute.
     * @param int|null   $fetchMode The fetch mode must be one of the
     *                              PDO::FETCH_* constants.
     * @param mixed|null $modeArg   Column number, class name or object.
     * @param array|null $ctorArgs  Constructor arguments.
     *
     * @return Oci8Statement
     * @throws \Sludio\HelperBundle\Doctrine\DBAL\Driver\OCI8\Oci8Exception
     */
    public function query($statement, $fetchMode = null, $modeArg = null, array $ctorArgs = [])
    {
        $stmt = $this->query($statement);
        if ($fetchMode) {
            $stmt->setFetchMode($fetchMode, $modeArg, $ctorArgs);
        }

        return $stmt;
    }

    /**
     * Method not implemented
     *
     * @param string $name Sequence name; no use in this context
     *
     * @return mixed Last sequence number or 0 if sequence does not exist
     * @throws \Sludio\HelperBundle\Doctrine\DBAL\Driver\OCI8\Oci8Exception
     */
    public function lastInsertId($name = null)
    {
        throw new Oci8Exception('Method not implemented');
    }

    /**
     * Fetch the SQLSTATE associated with the last operation on the database
     * handle
     * While this returns an error code, it merely emulates the action. If
     * there are no errors, it returns the success SQLSTATE code (00000).
     * If there are errors, it returns HY000. See errorInfo() to retrieve
     * the actual Oracle error code and message.
     *
     * @return string
     */
    public function errorCode()
    {
        $error = $this->errorInfo();

        return $error[0];
    }

    /**
     * Returns extended error information for the last operation on the database
     * handle
     * The array consists of the following fields:
     *   0  SQLSTATE error code (a five characters alphanumeric identifier
     *      defined in the ANSI SQL standard).
     *   1  Driver-specific error code.
     *   2  Driver-specific error message.
     *
     * @return array Error information
     */
    public function errorInfo()
    {
        $e = oci_error($this->dbh);

        if (is_array($e)) {
            return [
                'HY000',
                $e['code'],
                $e['message'],
            ];
        }

        return [
            '00000',
            null,
            null,
        ];
    }

    /**
     * Retrieve a database connection attribute
     *
     * @param int $attribute
     *
     * @return mixed A successful call returns the value of the requested PDO
     *   attribute. An unsuccessful call returns null.
     */
    public function getAttribute($attribute)
    {
        if ($attribute === PDO::ATTR_DRIVER_NAME) {
            return 'oci8';
        }

        if (isset($this->options[$attribute])) {
            return $this->options[$attribute];
        }

        return null;
    }

    /**
     * Special non PDO function used to start cursors in the database
     * Remember to call oci_free_statement() on your cursor
     *
     * @access public
     * @return mixed New statement handle, or FALSE on error.
     */
    public function getNewCursor()
    {
        return oci_new_cursor($this->dbh);
    }

    /**
     * Special non PDO function used to start descriptor in the database
     * Remember to call oci_free_statement() on your cursor
     *
     * @access public
     *
     * @param int $type One of OCI_DTYPE_FILE, OCI_DTYPE_LOB or OCI_DTYPE_ROWID.
     *
     * @return mixed New LOB or FILE descriptor on success, FALSE on error.
     */
    public function getNewDescriptor($type = OCI_D_LOB)
    {
        return oci_new_descriptor($this->dbh, $type);
    }

    /**
     * Special non PDO function used to close an open cursor in the database
     *
     * @access public
     *
     * @param mixed $cursor A valid OCI statement identifier.
     *
     * @return mixed Returns TRUE on success or FALSE on failure.
     */
    public function closeCursor($cursor)
    {
        return oci_free_statement($cursor);
    }

    /**
     * Special non PDO function
     * Allocates new collection object
     *
     * @param string $typeName Should be a valid named type (uppercase).
     * @param string $schema   Should point to the scheme, where the named type was created.
     *                         The name of the current user is the default value.
     *
     * @return \OCI_Collection
     */
    public function getNewCollection($typeName, $schema)
    {
        return oci_new_collection($this->dbh, $typeName, $schema);
    }

    /**
     * Places quotes around the input string
     *  If you are using this function to build SQL statements, you are strongly
     * recommended to use prepare() to prepare SQL statements with bound
     * parameters instead of using quote() to interpolate user input into an SQL
     * statement. Prepared statements with bound parameters are not only more
     * portable, more convenient, immune to SQL injection, but are often much
     * faster to execute than interpolated queries, as both the server and
     * client side can cache a compiled form of the query.
     *
     * @param string $string    The string to be quoted.
     * @param int    $paramType Provides a data type hint for drivers that have
     *                          alternate quoting styles
     *
     * @return string Returns a quoted string that is theoretically safe to pass
     *   into an SQL statement.
     */
    public function quote($string, $paramType = PDO::PARAM_STR)
    {
        return "'".str_replace("'", "''", $string)."'";
    }

    /**
     * Special non PDO function to check if sequence exists
     *
     * @param  string $name
     *
     * @return boolean
     * @throws \Sludio\HelperBundle\Doctrine\DBAL\Driver\OCI8\Oci8Exception
     */
    public function checkSequence($name)
    {
        if (!$name) {
            return false;
        }

        $stmt = $this->query("select count(*)
            from all_sequences
            where
                sequence_name=upper('{$name}')
                and sequence_owner=upper(user)
            ", PDO::FETCH_COLUMN);

        return $stmt->fetch();
    }

    /**
     * Parse DSN string and get $param value.
     *
     * @param string $dsn
     * @param string $param
     * @param mixed  $default
     *
     * @return string|null
     */
    protected function parseDsn($dsn, $param, $default = null)
    {
        if (preg_match('/'.$param.'=(?<param>[^;]+)/', $dsn, $mathes)) {
            return $mathes['param'];
        }

        return $default;
    }

    /**
     * Connect to database
     *
     * @param string $username
     * @param string $password
     * @param        $host
     * @param string $dbName
     * @param        $port
     * @param string $charset
     * @param array  $options
     *
     * @throws \Sludio\HelperBundle\Doctrine\DBAL\Driver\OCI8\Oci8Exception
     */
    public function connect($username, $password, $host, $dbName, $port, $charset, array $options = [])
    {
        $dbName = '(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(Host = '.$host.') (Port = '.$port.')) (CONNECT_DATA = (SID = '.$dbName.')))';

        if (array_key_exists(PDO::ATTR_PERSISTENT, $options) && $options[PDO::ATTR_PERSISTENT]) {
            $this->dbh = oci_pconnect($username, $password, $dbName, $charset);
        } else {
            $this->dbh = oci_connect($username, $password, $dbName, $charset);
        }

        if (!$this->dbh) {
            $e = oci_error();
            throw new Oci8Exception($e['message']);
        }
    }
}
