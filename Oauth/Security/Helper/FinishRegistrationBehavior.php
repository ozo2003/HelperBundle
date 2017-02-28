<?php

namespace Sludio\HelperBundle\Oauth\Security\Helper;

use Sludio\HelperBundle\Oauth\Security\Exception\FinishRegistrationException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

trait FinishRegistrationBehavior
{
    protected function saveUserInfoToSession(Request $request, FinishRegistrationException $e)
    {
        $request->getSession()->set(
            'guard.finish_registration.user_information',
            $e->getUserInformation()
        );
    }
    
    public function getUserInfoFromSession(Request $request)
    {
        return $request->getSession()->get('guard.finish_registration.user_information');
    }
}
