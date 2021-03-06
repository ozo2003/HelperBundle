<?php

namespace Sludio\HelperBundle\Sitemap\Entity;

class SitemapIndex
{
    protected $loc;
    protected $lastmod;
    protected $urlCount = 0;

    public function getLoc()
    {
        return $this->loc;
    }

    public function setLoc($loc)
    {
        if (\strlen($loc) > 2048) {
            throw new \DomainException('The loc value must be less than 2,048 characters');
        }

        $this->loc = $loc;

        return $this;
    }

    public function getLastmod()
    {
        if ($this->lastmod === null) {
            return null;
        }

        return $this->lastmod->format(\DateTime::W3C);
    }

    public function setLastmod($lastmod)
    {
        if ($lastmod !== null && !$lastmod instanceof \DateTime) {
            $lastmod = new \DateTime($lastmod);
        }

        $this->lastmod = $lastmod;

        return $this;
    }

    public function incrementUrl()
    {
        $this->urlCount++;
    }

    public function getUrlCount()
    {
        return $this->urlCount;
    }
}
