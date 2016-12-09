<?php

namespace Sludio\HelperBundle\Position\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TreeController extends Controller
{
    public function downAction($class, $id)
    {
        $em = $this->getDoctrine()->getManager($this->container->getParameter('sludio_helper.entity_manager'));
        $repo = $em->getRepository($class);
        $object = $repo->findOneById($id);
        if ($object->getParent()) {
            $repo->moveDown($object);
        }

        return $this->redirect($this->getRequest()->headers->get('referer'));
    }

    public function upAction($class, $id)
    {
        $em = $this->getDoctrine()->getManager($this->container->getParameter('sludio_helper.entity_manager'));
        $repo = $em->getRepository($class);
        $object = $repo->findOneById($id);
        if ($object->getParent()) {
            $repo->moveUp($object);
        }

        return $this->redirect($this->getRequest()->headers->get('referer'));
    }
}
