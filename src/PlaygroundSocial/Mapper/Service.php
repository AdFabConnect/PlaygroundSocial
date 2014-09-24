<?php

namespace PlaygroundSocial\Mapper;

use Doctrine\ORM\QueryBuilder;
use PlaygroundSocial\Entity\Service as ServiceEntity;

class Service extends EntityMapper
{

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundSocial\Entity\Service');
        }

        return $this->er;
    }
}