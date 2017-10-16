<?php

namespace Sludio\HelperBundle\Position\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class TreeController extends Controller
{
    public function downAction($class, $id)
    {
        $em = $this->getDoctrine()->getManager($this->container->getParameter('sludio_helper.entity.manager'));
        $map = $this->container->getParameter('sludio_helper.position.field')['entities'];
        if (empty($map)) {
            throw new InvalidConfigurationException('Please configure sludio_helper.position.field.enties to use move functionality');
        }
        $class = $map[$class];
        $repo = $em->getRepository($class);
        $object = $repo->findOneById($id);
        if ($object->getParent()) {
            $repo->moveDown($object);
        }

        return $this->redirect($this->getRequest()->headers->get('referer'));
    }

    public function upAction($class, $id)
    {
        $em = $this->getDoctrine()->getManager($this->container->getParameter('sludio_helper.entity.manager'));
        $map = $this->container->getParameter('sludio_helper.position.field')['entities'];
        if (empty($map)) {
            throw new InvalidConfigurationException('Please configure sludio_helper.position.field.enties to use move functionality');
        }
        $class = $map[$class];
        $repo = $em->getRepository($class);
        $object = $repo->findOneById($id);
        if ($object->getParent()) {
            $repo->moveUp($object);
        }

        return $this->redirect($this->getRequest()->headers->get('referer'));
    }
}
