<?php

namespace Sludio\HelperBundle\Sitemap\Dumper;

class GzFileDumper implements DumperFileInterface
{
    use FileDumperTrait;

    /**
     * {@inheritdoc}
     */
    public function dump($string)
    {
        if ($this->handle === null) {
            $this->openFile();
        }

        gzwrite($this->handle, $string);
    }

    protected function openFile()
    {
        $this->handle = gzopen($this->filename, 'w9');

        if ($this->handle === false) {
            throw new \RuntimeException(sprintf('Impossible to open the file %s in write mode', $this->filename));
        }
    }
}
