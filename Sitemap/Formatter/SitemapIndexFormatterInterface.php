<?php

namespace Sludio\HelperBundle\Sitemap\Formatter;

use Sludio\HelperBundle\Sitemap\Entity\SitemapIndex;

interface SitemapIndexFormatterInterface extends FormatterInterface
{
    public function getSitemapIndexStart();

    public function getSitemapIndexEnd();

    public function formatSitemapIndex(SitemapIndex $sitemapIndex);
}