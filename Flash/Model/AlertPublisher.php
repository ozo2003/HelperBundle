<?php

namespace Sludio\HelperBundle\Flash\Model;

class AlertPublisher implements \Iterator, \ArrayAccess, \Countable
{
    private $alerts;

    public function __construct(AlertManagerInterface $alertManager)
    {
        $this->alerts = $alertManager->getAlerts();
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return current($this->alerts);
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        next($this->alerts);
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return key($this->alerts);
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return key($this->alerts) !== null;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        return reset($this->alerts);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return isset($this->alerts[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return isset($this->alerts[$offset]) ? $this->alerts[$offset] : null;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->$this->alerts[$offset] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->alerts[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->alerts);
    }
}
