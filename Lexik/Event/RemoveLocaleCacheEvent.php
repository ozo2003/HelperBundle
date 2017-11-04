<?php

namespace Sludio\HelperBundle\Lexik\Event;

use Symfony\Component\EventDispatcher\Event;

class RemoveLocaleCacheEvent extends Event
{
    /**
     * @const String
     */
    const PRE_REMOVE_LOCAL_CACHE = 'pre_remove_local_cache.event';

    /**
     * @const String
     */
    const POST_REMOVE_LOCAL_CACHE = 'post_remove_local_cache.event';

    /**
     * @var array
     */
    private $managedLocales = [];

    public function __construct(array $managedLocales)
    {
        $this->managedLocales = $managedLocales;
    }

    /**
     * @return array
     */
    public function getManagedLocales()
    {
        return $this->managedLocales;
    }
}
