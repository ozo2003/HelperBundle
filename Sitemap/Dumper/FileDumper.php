<?php

namespace Sludio\HelperBundle\Sitemap\Dumper;

class FileDumper implements DumperFileInterface
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

        fwrite($this->handle, $string);
    }

    protected function openFile()
    {
        $this->handle = fopen($this->filename, 'w+b');

        if ($this->handle === false) {
            throw new \RuntimeException(sprintf('Impossible to open the file %s in write mode', $this->filename));
        }
    }
}
