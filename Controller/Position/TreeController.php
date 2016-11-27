<?php

namespace Sludio\HelperBundle\Controller\Position;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TreeController extends Controller
{
    public function downAction($category_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository('ContentBundle:Category');
        $category = $repo->findOneById($category_id);
        if ($category->getParent()) {
            $repo->moveDown($category);
        }

        return $this->redirect($this->getRequest()->headers->get('referer'));
    }

    public function upAction($category_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository('ContentBundle:Category');
        $category = $repo->findOneById($category_id);
        if ($category->getParent()) {
            $repo->moveUp($category);
        }

        return $this->redirect($this->getRequest()->headers->get('referer'));
    }
}
