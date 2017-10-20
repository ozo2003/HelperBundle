<?php

namespace Sludio\HelperBundle\Position\Service;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;

class PositionHandler
{
    protected $positionField;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getLastPosition($entity)
    {
        $query = $this->entityManager->createQuery(sprintf('SELECT MAX(m.%s) FROM %s m', $positionFiles = $this->getPositionFieldByEntity($entity), $entity));
        $result = $query->getResult();

        if (array_key_exists(0, $result)) {
            return intval($result[0][1]);
        }

        return 0;
    }

    /**
     * @param mixed $positionField
     */
    public function setPositionField($positionField)
    {
        $this->positionField = $positionField;
    }

    /**
     * @param $entity
     *
     * @return string
     */
    public function getPositionFieldByEntity($entity)
    {
        if (is_object($entity)) {
            $entity = ClassUtils::getClass($entity);
        }
        if (isset($this->positionField['entities'][$entity])) {
            return $this->positionField['entities'][$entity];
        } else {
            return $this->positionField['default'];
        }
    }

    /**
     * @param $object
     * @param $position
     * @param $lastPosition
     *
     * @return int
     */
    public function getPosition($object, $position, $lastPosition)
    {
        $getter = sprintf('get%s', ucfirst($this->getPositionFieldByEntity($object)));
        $newPosition = 0;

        switch ($position) {
            case 'up':
                if ($object->{$getter}() > 0) {
                    $newPosition = $object->{$getter}() - 1;
                }
                break;

            case 'down':
                if ($object->{$getter}() < $lastPosition) {
                    $newPosition = $object->{$getter}() + 1;
                }
                break;

            case 'top':
                if ($object->{$getter}() > 0) {
                    $newPosition = 0;
                }
                break;

            case 'bottom':
                if ($object->{$getter}() < $lastPosition) {
                    $newPosition = $lastPosition;
                }
                break;
        }

        return $newPosition;
    }
}
