<?php

namespace PlaygroundSocial\Mapper;

use Doctrine\ORM\QueryBuilder;
use PlaygroundSocial\Entity\Element as ElementEntity;

class Element extends EntityMapper
{

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundSocial\Entity\Element');
        }

        return $this->er;
    }
}