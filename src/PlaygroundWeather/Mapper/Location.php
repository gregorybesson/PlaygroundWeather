<?php

namespace PlaygroundWeather\Mapper;

use Doctrine\ORM\AbstractQuery as Query;

class Location
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
            $this->er = $this->em->getRepository('\PlaygroundWeather\Entity\Location');
        }

        return $this->er;
    }

    public function queryAll($sortArray = array())
    {
        $query = $this->em->createQuery(
            'SELECT l FROM PlaygroundWeather\Entity\Location l '
            .( ! empty($sortArray) ? 'ORDER BY l.'.key($sortArray).' '.current($sortArray) : '' )
        );
        return $query;
    }

    public function queryPartialByCountry($country, $sortArray = array())
    {
        $query = $this->em->createQuery(
            'SELECT l FROM PlaygroundWeather\Entity\Location l
            WHERE l.country LIKE \'%'.$country.'%\'
            ORDER BY l.city ASC'
        );
        return $query;
    }

    public function queryCustom($filterArray = array(), $sortArray = array())
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder->select('l' );
        $queryBuilder->from('PlaygroundWeather\Entity\Location', 'l');
        if (!empty($filterArray)) {
            $item = current($filterArray);
            $queryBuilder->where('l.'.key($item). ' LIKE \'%' .current($item).'%\'');
            while(next($filterArray)) {
                $item = current($filterArray);
                $queryBuilder->andwhere('l.'.key($item). ' LIKE \'%' .current($item).'%\'');
            }
        }
        if (!empty($sortArray)) {
            $queryBuilder->orderBy('l.'.key($sortArray), current($sortArray));
        }
        return $queryBuilder->getQuery();
    }

    public function getCountries()
    {
        $query = $this->em->createQuery(
            'SELECT DISTINCT l.country FROM PlaygroundWeather\Entity\Location l
            ORDER BY l.country ASC'
        );
        return $query->getResult();
    }

    public function getDefaultLocation($sortArray = array())
    {
        $query = $this->em->createQuery(
            'SELECT l FROM PlaygroundWeather\Entity\Location l '
            .( ! empty($sortArray) ? 'ORDER BY l.'.key($sortArray).' '.current($sortArray) : '' )
        );
        $query->setMaxResults(1);
        return current($query->getResult(Query::HYDRATE_OBJECT));
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

    public function assertNoOther($location)
    {
        $results = $this->getEntityRepository()->findBy(
            array(
                'city' => $location->getCity(),
                'region' => $location->getRegion(),
                'country' => $location->getCountry()
            ));
        return (empty($results)) ? true : false;
    }
}