<?php

namespace Sludio\HelperBundle\Sitemap\Formatter;

use Sludio\HelperBundle\Sitemap\Entity\Url;

class TextFormatter extends BaseFormatter implements FormatterInterface
{
    public function getSitemapStart()
    {
        return '';
    }

    public function getSitemapEnd()
    {
        return '';
    }

    public function formatUrl(Url $url)
    {
        return $this->escape($url->getLoc())."\n";
    }
}
