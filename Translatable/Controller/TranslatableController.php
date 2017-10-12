<?php

namespace Sludio\HelperBundle\Translatable\Controller;

use Sludio\HelperBundle\Translatable\Repository\TranslatableRepository as Sludio;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class TranslatableController extends Controller
{
    public function generateAction()
    {
        Sludio::getAllTranslations();
        $data['success'] = 1;

        return new JsonResponse($data, 200, [
            'Cache-Control' => 'no-cache',
        ]);
    }
}
