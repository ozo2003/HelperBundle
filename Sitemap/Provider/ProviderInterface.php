<?php

namespace Sludio\HelperBundle\Sitemap\Provider;

use Sludio\HelperBundle\Sitemap\Sitemap;

interface ProviderInterface
{
    public function populate(Sitemap $sitemap);
}
