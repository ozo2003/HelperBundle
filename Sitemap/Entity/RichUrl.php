<?php

namespace Sludio\HelperBundle\Sitemap\Entity;

class RichUrl extends Url
{
    protected $alternateUrl = [];

    public function addAlternateUrl($locale, $url)
    {
        $this->alternateUrl[$locale] = $url;

        return $this;
    }

    public function getAlternateUrls()
    {
        return $this->alternateUrl;
    }
}
