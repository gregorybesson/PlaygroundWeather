<?php

namespace PlaygroundWeatherTest\Mapper;

use PlaygroundWeatherTest\Bootstrap;
use PlaygroundWeather\Entity\WeatherLocation;

class WeatherLocationTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundweather_weatherlocation_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testQueryAll()
    {
        $location1 = new WeatherLocation();
        $location1->setCity('aaaa');
        $location1->setLatitude(0);
        $location1->setLongitude(0);
        $this->tm->insert($location1);

        $location2 = new WeatherLocation();
        $location2->setCity('aaaz');
        $location2->setLatitude(0);
        $location2->setLongitude(1);
        $this->tm->insert($location2);

        $this->assertCount(2, $this->tm->queryAll()->getResult());
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