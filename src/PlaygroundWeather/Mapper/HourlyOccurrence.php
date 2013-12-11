<?php

namespace PlaygroundWeather\Mapper;

use PlaygroundWeather\Entity\DailyOccurrence as DailyOccurrenceEntity;

class HourlyOccurrence
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

     /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $er;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em      = $em;
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('\PlaygroundWeather\Entity\HourlyOccurrence');
        }

        return $this->er;
    }

    public function findById($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    public function findBy($array = array(), $sortArray = array())
    {
        return $this->getEntityRepository()->findBy($array, $sortArray);
    }

    public function insert($entity)
    {
        return $this->persist($entity);
    }

    public function update($entity)
    {
        return $this->persist($entity);
    }

    protected function persist($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    public function findAll()
    {
        return $this->getEntityRepository()->findAll();
    }

    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function findByDailyOccurrence(DailyOccurrenceEntity $dailyOccurrence, $sortArray = array())
    {
        return $this->getEntityRepository()->findBy(array('dailyOccurrence' => $dailyOccurrence), $sortArray);
    }

    public function findEveryCodeByDaily(DailyOccurrenceEntity $dailyOccurrence)
    {
        $query = $this->em->createQuery(
            'SELECT c.id FROM PlaygroundWeather\Entity\HourlyOccurrence AS h
            JOIN h.code AS c
            WHERE h.dailyOccurrence = :dailyOccurrence'
        );
        $query->setParameter('dailyOccurrence', $dailyOccurrence);
        return $query->getResult();
    }
}