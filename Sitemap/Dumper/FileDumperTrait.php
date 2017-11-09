<?php

namespace Sludio\HelperBundle\Sitemap\Dumper;

trait FileDumperTrait
{
    protected $filename;
    protected $handle;

    /**
     * Constructor.
     *
     * @param string $filename The filename. Must be acessible in write mode.
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilename($filename)
    {
        $this->clearHandle();
        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function clearHandle()
    {
        if ($this->handle !== null) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    public function __destruct()
    {
        $this->clearHandle();
    }
}
