<?php

namespace Sludio\HelperBundle\Sitemap\Formatter;

use Sludio\HelperBundle\Sitemap\Entity\Url;

interface FormatterInterface
{
    public function getSitemapStart();

    public function getSitemapEnd();

    public function formatUrl(Url $url);
}
