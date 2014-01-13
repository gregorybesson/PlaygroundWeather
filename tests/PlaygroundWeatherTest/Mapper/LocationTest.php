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

    public function testGetDefaultNoLocations()
    {
        $this->assertFalse($this->tm->getDefaultLocation());
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

        $this->tm->remove($location);
        $this->tm->remove($location1);
        $this->tm->remove($location2);
        $this->assertEmpty($this->tm->queryAll()->getResult());
    }

    public function testQueryCustomOrderBy()
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

        $this->assertCount(3, $this->tm->queryCustom()->getResult());
        $this->assertEquals($location1, current($this->tm->queryCustom(array(), array('city'=>'ASC'))->getResult()));
        $this->assertEquals($location2, current($this->tm->queryCustom(array(), array('city'=>'DESC'))->getResult()));

        $this->assertEquals($location2, current($this->tm->queryCustom(array(array('city' => 'z')))->getResult()));
        $this->assertCount(2, $this->tm->queryCustom(array(array('longitude' => '1')))->getResult());
        $this->assertEquals($location1, $this->tm->queryCustom(array(array('longitude' => '1'), array('latitude' => '1')))->getOneOrNullResult());
        $this->assertEquals($location1, current($this->tm->queryCustom(array(array('longitude' => '1')), array('city'=>'ASC'))->getResult()));
        $this->assertEquals($location2, current($this->tm->queryCustom(array(array('longitude' => '1')), array('city'=>'DESC'))->getResult()));

        $this->tm->remove($location);
        $this->tm->remove($location1);
        $this->tm->remove($location2);
        $this->assertEmpty($this->tm->queryCustom()->getResult());
    }

    public function testGetCountries()
    {
        $location = new Location();
        $location->setCity('xxxxx');
        $location->setCountry('france');
        $location->setLatitude(3);
        $location->setLongitude(3);
        $this->tm->insert($location);

        $location2 = new Location();
        $location2->setCity('yyyyy');
        $location2->setCountry('france');
        $location2->setLatitude(4);
        $location2->setLongitude(4);
        $this->tm->insert($location2);

        $location1 = new Location();
        $location1->setCity('wwwww');
        $location1->setCountry('spain');
        $location1->setLatitude(5);
        $location1->setLongitude(5);
        $this->tm->insert($location1);
        $this->assertCount(2, $this->tm->getCountries());
        $this->assertContains(array('country'=>'france'), $this->tm->getCountries());
        $this->assertContains(array('country'=>'spain'), $this->tm->getCountries());

        $this->tm->remove($location);
        $this->tm->remove($location1);
        $this->tm->remove($location2);
        $this->assertEmpty($this->tm->getCountries());
    }

    public function testQueryPartialByCountry()
    {
        $location = new Location();
        $location->setCity('xxxxx');
        $location->setCountry('france');
        $location->setLatitude(3);
        $location->setLongitude(3);
        $this->tm->insert($location);

        $location2 = new Location();
        $location2->setCity('yyyyy');
        $location2->setCountry('france');
        $location2->setLatitude(4);
        $location2->setLongitude(4);
        $this->tm->insert($location2);

        $location1 = new Location();
        $location1->setCity('wwwww');
        $location1->setCountry('spain');
        $location1->setLatitude(5);
        $location1->setLongitude(5);
        $this->tm->insert($location1);

        $this->assertCount(2, $this->tm->queryPartialByCountry('france')->getResult());
        $this->assertContains($location1, $this->tm->queryPartialByCountry('spain')->getResult());

        $result = $this->tm->queryPartialByCountry('france')->getResult();
        $this->assertEquals($location, current($result));
        $this->assertEquals($location2, end($result));

        $this->tm->remove($location);
        $this->tm->remove($location1);
        $this->tm->remove($location2);
        $this->assertEmpty($this->tm->queryPartialByCountry('france')->getResult());
        $this->assertEmpty($this->tm->queryPartialByCountry('spain')->getResult());
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