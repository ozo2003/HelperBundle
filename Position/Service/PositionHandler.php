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
        $query = $this->entityManager->createQuery(sprintf('SELECT MAX(m.%s) FROM %s m', $this->getPositionFieldByEntity($entity), $entity));
        $result = $query->getResult();

        if (array_key_exists(0, $result)) {
            return (int)$result[0][1];
        }

        return 0;
    }

    /**
     * @param $entity
     *
     * @return string
     */
    public function getPositionFieldByEntity($entity)
    {
        if (\is_object($entity)) {
            $entity = ClassUtils::getClass($entity);
        }
        if (isset($this->positionField['entities'][$entity])) {
            return $this->positionField['entities'][$entity];
        }

        return $this->positionField['default'];
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
     * @param $position
     * @param $lastPosition
     *
     * @return int
     */
    public function getPosition($entity, $position, $lastPosition)
    {
        $getter = sprintf('get%s', ucfirst($this->getPositionFieldByEntity($entity)));

        return (int)$this->{'sludio'.ucfirst($position)}($entity->{$getter}(), $lastPosition);
    }

    protected function sludioUp($actual)
    {
        if ($actual > 0) {
            return $actual - 1;
        }

        return $actual;
    }

    protected function sludioDown($actual, $last)
    {
        if ($actual < $last) {
            return $actual + 1;
        }

        return $actual;
    }

    protected function sludioTop($actual)
    {
        if ($actual > 0) {
            return 0;
        }

        return $actual;
    }

    protected function sludioBottom($actual, $last)
    {
        if ($actual < $last) {
            return $last;
        }

        return $actual;
    }
}
