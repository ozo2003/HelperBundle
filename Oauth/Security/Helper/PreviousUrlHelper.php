<?php

namespace Sludio\HelperBundle\Oauth\Security\Helper;

use Symfony\Component\HttpFoundation\Request;

trait PreviousUrlHelper
{
    public function getPreviousUrl(Request $request, $providerKey)
    {
        return $request->getSession()->get('_security.' . $providerKey . '.target_path');
    }
}
