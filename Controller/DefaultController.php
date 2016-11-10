<?php

namespace Sludio\HelperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SludioHelperBundle:Default:index.html.twig');
    }
}
