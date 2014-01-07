<?php

namespace PlaygroundWeatherTest\Mapper;

use PlaygroundWeatherTest\Bootstrap;
use PlaygroundWeather\Entity\Location;

class LocationTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundweather_location_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
        parent::setUp();
    }

    public function testQueryAll()
    {
        $location = new Location();
        $location->setCity('bbbbb');
        $location->setLatitude(2);
        $location->setLongitude(2);
        $this->tm->insert($location);

        $location2 = new Location();
        $location2->setCity('zzzz');
        $location2->setLatitude(0);
        $location2->setLongitude(1);
        $this->tm->insert($location2);

        $location1 = new Location();
        $location1->setCity('aaaa');
        $location1->setLatitude(1);
        $location1->setLongitude(1);
        $this->tm->insert($location1);


        $this->assertCount(3, $this->tm->queryAll()->getResult());
        $this->assertEquals($location1, current($this->tm->queryAll(array('city'=>'ASC'))->getResult()));
        $this->assertEquals($location2, current($this->tm->queryAll(array('city'=>'DESC'))->getResult()));
    }


    public function tearDown()
    {
        $dbh = $this->em->getConnection();
        unset($this->tm);
        unset($this->sm);
        unset($this->em);
        parent::tearDown();
    }
}