<?php

namespace Sludio\HelperBundle\Sitemap\Formatter;

abstract class BaseFormatter
{
    protected function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES);
    }
}
