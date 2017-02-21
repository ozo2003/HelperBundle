<?php

namespace Sludio\HelperBundle\Translatable\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sludio\HelperBundle\Translatable\Repository\TranslatableRepository as Sludio;

class TranlsatableController extends Controller
{
    public function generateAction()
    {
        Sludio::getAllTranslations();
        $data['success'] = 1;

        return new JsonResponse($data, 200, array(
            'Cache-Control' => 'no-cache',
        ));
    }
}
