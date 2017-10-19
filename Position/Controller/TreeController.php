<?php

namespace Sludio\HelperBundle\Position\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class TreeController extends Controller
{
    public function moveAction(Request $request, $class, $id, $position)
    {
        $entityManager = $this->getDoctrine()
            ->getManager($this->container->getParameter('sludio_helper.entity.manager'))
        ;
        $map = $this->container->getParameter('sludio_helper.position.field')['entities'];
        if (empty($map)) {
            throw new InvalidConfigurationException('Please configure sludio_helper.position.field.enties to use move functionality');
        }
        $class = $map[$class];
        $repo = $entityManager->getRepository($class);
        $object = $repo->findOneById($id);
        if ($object->getParent()) {
            $repo->{'move'.ucfirst($position)}($object);
        }

        return $this->redirect($request->headers->get('referer'));
    }
}
