<?php

namespace Sludio\HelperBundle\Sitemap\Dumper;

class MemoryDumper implements DumperInterface
{
    protected $buffer = '';

    /**
     * Dump a string into the buffer.
     *
     * @param string $string The string to dump.
     *
     * @return string The current buffer.
     */
    public function dump($string)
    {
        $this->buffer .= $string;

        return $this->buffer;
    }
}
