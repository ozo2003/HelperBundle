<?php

namespace Sludio\HelperBundle\Sitemap\Dumper;

interface DumperFileInterface extends DumperInterface
{
    /**
     * Set the filename
     *
     * @param string $filename The filename.
     */
    public function setFilename($filename);

    /**
     * Get the filename
     */
    public function getFilename();

    /**
     * Clear the file handle
     */
    public function clearHandle();
}
